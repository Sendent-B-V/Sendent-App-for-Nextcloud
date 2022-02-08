import './retentionAssistant.scss';
import { loadState } from '@nextcloud/initial-state';
import confirmPassword from '@nextcloud/password-confirmation';
import { translate as t, translatePlural as p } from '@nextcloud/l10n'
import logger from './utils/logger';
import { api } from './common/api';
import { generateUrl } from '@nextcloud/router';

const CONFIG_UPLOAD_TAG = 'tag:upload';
const CONFIG_REMOVED_TAG = 'tag:removed';
const CONFIG_EXPIRED_TAG = 'tag:expired';

const REMOVED_DEFAULT_AMOUNT = 30;
const EXPIRED_DEFAULT_AMOUNT = 90;
const DEFAULT_NAMES = {
    [CONFIG_UPLOAD_TAG]: 'outlook:upload',
    [CONFIG_REMOVED_TAG]: 'retention:removed',
    [CONFIG_EXPIRED_TAG]: 'retention:expired',
};

class RetentionAssistant {
    private apps: { [id: string]: string };

    private tags: { [id: string]: number }

    constructor(private rootElement: JQuery) {
        this.apps = loadState('sendent', 'apps');
        this.tags = loadState('sendent', 'tags');

        rootElement.find('.sendent-is-loading').remove();
        rootElement.find('.hidden').removeClass('hidden');
    }

    public isConfigured(): boolean {
        return this.tags[CONFIG_UPLOAD_TAG] > 0 &&
            this.tags[CONFIG_REMOVED_TAG] > 0 &&
            this.tags[CONFIG_EXPIRED_TAG] > 0;
    }

    public start() {
        this.nextStep(0).catch((err) => {
            logger.error('Error while executing retention assistant', {err});
        });
    }

    public async nextStep(index: number) {
        const stepElement = this.rootElement.find('.sendent-steps > li').eq(index);

        if (stepElement.length === 0) {
            console.log('Done');
            return;
        }

        const action = stepElement.attr('data-action') + 'Action';

        if (!this[action]) {
            throw new Error(`Unknown action at step ${index}: ${action}`);
        }

        stepElement.attr('data-state', 'active');

        try {
            await this[action](stepElement);
        } catch(err) {
            console.log(`Error while executing action "${action}": `, err);
            return;
        }

        stepElement.attr('data-state', 'success');

        return this.nextStep(index + 1);
    }

    public automatedTaggingAppAction(element: JQuery) {
        return this.handleAppCheck(
            element,
            'files_automatedtagging',
            'Files automated tagging',
            'workflow'
        );
    }

    public async workflowAction(element: JQuery) {
        let tagIds: number[];

        try {
            tagIds = await this.getUploadTagIdsFromWorkflow();
        } catch(err) {
            element.attr('data-state', 'fail');
            element.html(t('sendent', 'Could not get existing workflows'));

            throw new Error(err);
        }

        if (tagIds.includes(this.tags[CONFIG_UPLOAD_TAG])) {
            const workflowTag = await api.getTag(this.tags[CONFIG_UPLOAD_TAG]);

            element.text(t('sendent', 'Workflow configured for automated file tagging'));
            element.append('<br />');
            element.append($('<em>').text(t('sendent', 'All uploaded files are tagged with "{name}"', {name: workflowTag.name})));

            return;
        }

        if (tagIds.length === 0) {
            element.attr('data-state', 'fail');
            element.html(t('sendent', 'Workflow not configured for automated file tagging. <a href="#" class="button">Add rule</a>'));

            return new Promise(resolve => {
                element.find('a').on('click', (ev) => {
                    ev.preventDefault();

                    element.attr('data-state', 'active');
                    element.text(t('sendent', 'Create workflow rule'));

                    resolve(this.addWorkflowRule());
                });
            });
        }

        const configuredTag = this.tags[CONFIG_UPLOAD_TAG] > -1 ? await api.getTag(this.tags[CONFIG_UPLOAD_TAG]) : undefined;
        const tags = {};

        for (const tagId of tagIds) {
            tags[tagId] = await api.getTag(tagId);
        }

        element.attr('data-state', 'fail');

        element.html(p(
            'sendent',
            'We found one workflow, but it is using a different tag than configured.',
            'We found multiple workflows, but non of them is using the configured tag.',
            tagIds.length
        ));

        $('<a>').attr('data-tag', 'newTag').text(t('sendent', 'Create workflow with new tag')).appendTo(element);

        for (const tagId of tagIds) {
            $('<a>').attr('data-tag', tagId).text(t('sendent', 'Use rule with "{name}"', {name: tags[tagId].name})).appendTo(element);
        }

        if (configuredTag) {
            $('<a>').attr('data-tag', 'configuredTag').text(t('sendent', 'Create workflow with "{name}"', {name: configuredTag.name})).appendTo(element);
        }

        element.find('a').addClass('button');

        return new Promise(resolve => {
            element.find('a').on('click', (ev) => {
                ev.preventDefault();

                element.attr('data-state', 'active');
                element.text(t('sendent', 'Configure workflow rule'));

                const linkElement = $(ev.target);
                const tag = <string>linkElement.attr('data-tag');

                if (tag === 'configuredTag') {
                    resolve(this.addWorkflowRule(this.tags[CONFIG_UPLOAD_TAG]));
                } else if (tag === 'newTag') {
                    resolve(this.addWorkflowRule());
                } else {
                    resolve(this.setUploadTagId(parseInt(tag, 10)));
                }
            });
        });
    }

    public async retentionAppAction(element: JQuery) {
        return this.handleAppCheck(
            element,
            'files_retention',
            'Retention',
            'tools'
        );
    }

    public async retentionAction(element: JQuery) {
        const retentions = await api.getRetentions();
        let hasRemovedTag = false;
        let hasExpiredTag = false;

        for (const retention of retentions) {
            if (retention.tagid === this.tags[CONFIG_REMOVED_TAG]) {
                hasRemovedTag = true;
            }

            if (retention.tagid === this.tags[CONFIG_EXPIRED_TAG]) {
                hasExpiredTag = true;
            }
        }

        if (hasRemovedTag && hasExpiredTag) {
            const workflowTag = await api.getTag(this.tags[CONFIG_UPLOAD_TAG]);
            const removedTag = await api.getTag(this.tags[CONFIG_REMOVED_TAG]);
            const expiredTag = await api.getTag(this.tags[CONFIG_EXPIRED_TAG]);

            element.text(t('sendent', 'Retention rules configured'));
            element.append('<br />');
            element.append(
                $('<em>').text(t(
                    'sendent',
                    'Expired or removed shares which are tagged with "{workflowName}" are getting the additional tag "{expiredName}" respectively "{removedName}"',
                    {workflowName: workflowTag.name, expiredName: expiredTag.name, removedName: removedTag.name}
                ))
            );

            return;
        }

        element.attr('data-state', 'fail');

        const text: string[] = [];

        if (!hasRemovedTag) {
            text.push(t('sendent', 'No rule for removed shares.'));
        }

        if (!hasExpiredTag) {
            text.push(t('sendent', 'No rule for expired shares.'));
        }

        text.push(p('sendent', '<a href="#" class="button">Add rule</a>', '<a href="#" class="button">Add rules</a>', hasRemovedTag === hasExpiredTag ? 2 : 1));

        element.html(text.join(' '));

        return new Promise(resolve => {
            element.find('a').on('click', (ev) => {
                ev.preventDefault();

                element.attr('data-state', 'active');
                element.text(t('sendent', 'Create retention rules'));

                resolve(this.createRetentionRules(hasRemovedTag, hasExpiredTag));
            });
        });
    }

    private handleAppCheck(container: JQuery, appId: string, appName: string, appCategory: string) {
        if (this.apps[appId]) {
            return new Promise<void>(resolve => {
                // prevent flickering
                setTimeout(() => {
                    container.text(t('sendent', '{name} app installed', { name: appName }));
                    resolve();
                }, 2000);
            });
        }

        return new Promise(() => {
            container.attr('data-state', 'fail');
            container.html(t('sendent', 'Please install the <a href="{url}" class="button">{name}</a> app', {
                name: appName,
                url: generateUrl(`/settings/apps/${appCategory}/${appName}`),
            }));
        });
    }

    private async createRetentionRules(hasRemovedTag: boolean, hasExpiredTag: boolean) {
        if (!hasRemovedTag) {
            await this.prepareTag(CONFIG_REMOVED_TAG);

            await api.createRetention(this.tags[CONFIG_REMOVED_TAG], REMOVED_DEFAULT_AMOUNT);
        }

        if (!hasExpiredTag) {
            await this.prepareTag(CONFIG_EXPIRED_TAG);

            await api.createRetention(this.tags[CONFIG_EXPIRED_TAG], EXPIRED_DEFAULT_AMOUNT);
        }
    }

    private async addWorkflowRule(tagId?: number) {
        if (!tagId) {
            tagId = await this.prepareTag(CONFIG_UPLOAD_TAG);
        }

        return api.createWorkflow(tagId);
    }

    private async setUploadTagId(tagId: number) {
        await confirmPassword();

        OCP.AppConfig.setValue('sendent', CONFIG_UPLOAD_TAG, tagId);

        this.tags[CONFIG_UPLOAD_TAG] = tagId;
    }

    private async prepareTag(key: string) {
        if (this.tags[key] < 0) {
            const tag = await api.createTag(DEFAULT_NAMES[key]);

            await confirmPassword();

            OCP.AppConfig.setValue('sendent', key, tag.id);

            this.tags[key] = tag.id;
        }

        return this.tags[key];
    }

    private async getUploadTagIdsFromWorkflow(): Promise<number[]> {
        const workflows = await api.getWorkflows();
        const taggingWorkflows = workflows['OCA\\FilesAutomatedTagging\\Operation'] || [];
        const tagIds: number[] = [];

        for (const workflow of taggingWorkflows) {
            if (workflow.checks.length === 1) {
                const check = workflow.checks[0];

                if (check.class === 'OCA\\WorkflowEngine\\Check\\RequestUserAgent' &&
                    check.operator === 'is' &&
                    check.value === 'mail') {
                    const tagId = parseInt(workflow.operation, 10);

                    tagIds.push(tagId);
                }
            }
        }

        return tagIds;
    }
}

$(() => {
    const rootElement = $('#sendent-retention-assistant');

    if (rootElement.length === 0) {
        return;
    }

    const assistant = new RetentionAssistant(rootElement);

    if (assistant.isConfigured()) {
        rootElement.find('a[href="#assistant"]').text(t('sendent', 'Check configuration'));
    }

    rootElement.find('a[href="#assistant"]').on('click', (ev) => {
        $(ev.target).remove();

        assistant.start();
    });
})
