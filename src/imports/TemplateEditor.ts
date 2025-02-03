/* eslint-disable @nextcloud/no-deprecations */
import { translate as t } from '@nextcloud/l10n'
import tinymce from 'tinymce';

/* Default icons are required for TinyMCE 5.3 or above */
import 'tinymce/icons/default';

/* A theme is also required */
import 'tinymce/themes/silver';

/* Import the skin */
import 'tinymce/skins/ui/oxide/skin.css';

import 'tinymce/plugins/code';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import 'tinymce/plugins/fullpage';
import 'tinymce/plugins/image';
import 'tinymce/plugins/save';

/* Import content css */
import 'tinymce/skins/ui/oxide/content.css';
import { generateFilePath } from '@nextcloud/router';
import axios from '@nextcloud/axios';

const tags = {
    URL: t('sendent', 'URL'),
    PASSWORD: t('sendent', 'Password'),
    EXPIRATIONDATE: t('sendent', 'Expiration date'),
    RECIPIENTS: t('sendent', 'Recipients'),
    FROM: t('sendent', 'From'),
    SUBJECT: t('sendent', 'Subject'),
    FILES: t('sendent', 'Files'),
    CURRENTDATE: t('sendent', 'Current date'),
    CURRENTTIME: t('sendent', 'Current time'),
    EXTRA: t('sendent', 'Extra'),
}

const LOGO_VAR = '{LOGO}';

export default class TemplateEditor {
    constructor(private element: HTMLTextAreaElement, private logoUrl: string) {
        this.replaceLogoVariable();
        this.init();
    }

    private replaceLogoVariable() {
        this.element.value = this.element.value.replaceAll(LOGO_VAR, this.logoUrl);
    }

    private init() {
        const element = this.element;
        const logoUrl = this.logoUrl;

        tinymce.init({
            skin: false,
            target: element,
            convert_urls: false,
            content_css: false,
            height: 600,
            width: 700,
            resize: 'both',
            plugins: 'code link lists table image save',
            menubar: 'edit view format tools table',
            toolbar: 'save | undo redo | styleselect | bold italic | link image | sendentLogo sendentTags sendentReset',
            setup: function (editor) {
                editor.ui.registry.addMenuButton('sendentTags', {
                    text: t('sendent', 'Tags'),
                    fetch: function (callback) {
                        const items = Object.keys(tags).map(variable => {
                            return {
                                type: 'menuitem' as any,
                                text: tags[variable],
                                onAction: function () {
                                    editor.insertContent(`{${variable}}`);
                                }
                            }
                        });

                        callback(items);
                    },
                });

                editor.ui.registry.addButton('sendentReset', {
                    text: t('sendent', 'Reset'),
                    onAction: function() {
                        const url = generateFilePath('sendent', 'assets', `templates/${element.id}.html`);

                        axios.get<string>(url).then(response => {

                            editor.setContent(response.data.replaceAll(LOGO_VAR, logoUrl));
                            editor.setDirty(true);
                            editor.undoManager.add();
                        });
                    },
                });

                editor.ui.registry.addButton('sendentLogo', {
                    text: t('sendent', 'Logo'),
                    onAction: function() {
                        editor.insertContent(`<img width='120' height='28' src='${logoUrl}'>`, {format: 'raw'});
                    },
                });

                addEventListener('beforeunload', (ev) => {
                    if (editor.isDirty()) {
                        ev.preventDefault();

                        return ev.returnValue = t('sendent', 'You have unsaved changes');
                    }
                }, {capture: true})
            },
            save_onsavecallback: function () {
                this.save();

                $(element).trigger('change');
            },
            urlconverter_callback: function(url, node: any, on_save, name) {
                if (url === logoUrl && node === 'img' && name === 'src') {
                    return LOGO_VAR;
                }

                return url;
            }
        })
    }
}
