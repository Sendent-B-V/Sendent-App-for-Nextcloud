import './filelist.scss';
import { generateRemoteUrl, generateUrl } from '@nextcloud/router';
import { getNavigation, Node as NCNode } from '@nextcloud/files';
import { subscribe } from '@nextcloud/event-bus';
import axios from '@nextcloud/axios';
import sanitizeHtml from 'sanitize-html';

const FOOTER_NAME = '.SECUREMAIL.html';
const CONTENT_ID = '#sendent-content';

const SANITIZE_OPTIONS = {
    allowedTags: sanitizeHtml.defaults.allowedTags.concat(['img', 'html', 'head', 'body', 'meta', 'style']),
    allowedAttributes: {
        ...sanitizeHtml.defaults.allowedAttributes,
        '*': ['class', 'style', 'colspan', 'width', 'border', 'valign', 'cellspacing', 'cellpadding', 'align', 'height', 'rowspan', 'rowspacing', 'rowpadding'],
        img: ['src', 'width', 'height', 'style', 'id'],
        html: ['xmlns*'],
        meta: ['name', 'content'],
    },
    allowVulnerableTags: true,
    allowedSchemes: ['data'],
    allowedSchemesByTag: {
        a: ['http', 'https', 'mailto', 'tel'],
    }
};

/**
 * FooterFile: This class is used to display a preview of a securemail file at the bottom of the files list
 */
class FooterFile {
    constructor(private file: NCNode) {
    }

    public async appendBelowFiles(): Promise<void> {

        console.log('sendent: Appending a preview of the securemail file at the bottom of the files list');

        $(CONTENT_ID).remove();

        const containerElement = $('<div>');
        containerElement.attr('id', 'sendent-content');
        if (OCA.Sharing?.PublicApp) {
            containerElement.insertAfter($('.files-filestable'));
        } else {
            containerElement.insertAfter($('.files-list__table'));
        }

        const loadingElement = $('<span>').addClass('icon-loading').css('position', 'unset');
        loadingElement.appendTo(containerElement);

        const path = this.getFilePath();
        const response = await axios.get(path);
        let content = response.data;
        content = sanitizeHtml(content, SANITIZE_OPTIONS);

        const iframeElement = this.generateIframeElement(content);
        containerElement.empty().append(iframeElement);
    }

    private getFilePath(): string {
        //console.log(this.file); // Added this so i could analyse the properties of the "file" object
        const path = encodeURI(this.file.path);
        const source = encodeURI(this.file.source); //Added this so i get full link to file
        const name = encodeURIComponent(this.file.basename);

        if (OCA.Sharing?.PublicApp) {
            const token = $('#sharingToken').val();

            return generateUrl('/s/{token}/download?path={path}&files={name}',
                {
                    token,
                    path,
                    name,
                }
            );
       }

        //return generateRemoteUrl('files/' + name); // Replaced this to generate remoteUrl based on filename to
        return source;
    }

    private generateIframeElement(content: string) {
        const iframeElement = $<HTMLFrameElement>('<iframe>');
        iframeElement.width(0);
        iframeElement.height(0);
        iframeElement.on('load', () => {
            const innerHeight = iframeElement.get(0)?.contentDocument?.documentElement?.scrollHeight;
            const innerWidth = iframeElement.get(0)?.contentDocument?.documentElement?.scrollWidth;

            innerHeight && iframeElement.height(innerHeight);
            innerWidth && iframeElement.width(innerWidth);
        });
        iframeElement.attr('srcdoc', content);

        return iframeElement
    }
}

/**
 * Searches for a securemail file, and, when found, display its sanitized content below the file list
 */
async function onFileListUpdated() {

    // function to block script execution
    const wait = t => new Promise((resolve, reject) => setTimeout(resolve, t))

    let fileList;
    if (OCA.Sharing?.PublicApp) {
        await wait(1500);
        fileList = OCA.Sharing.PublicApp.fileList.files.map((f) => {
            f.basename = f.name;
            return f;
        });
    } else {
        const currentPath = OCP.Files.Router.query.dir ?? '/';
        const view = getNavigation().active;
        const content = await view?.getContents(currentPath);
        fileList = content?.contents ?? [];
    }

    for (const file of fileList ?? []) {
        if (file.type === 'file' && file.basename === FOOTER_NAME) {
            const footerFile = new FooterFile(file);
            footerFile.appendBelowFiles();
        }
    }

}

/**
 * Initializes the securemail previewer
 */
function initSecureMailPreviewer() {
    console.log('sendent: initialising securemail previewer')
    subscribe('files:list:updated', (node: NCNode) => onFileListUpdated())
    onFileListUpdated();
}

/**
 * entry point
 */
if (document.readyState === 'complete') {
    initSecureMailPreviewer();
} else {
    document.addEventListener('DOMContentLoaded', initSecureMailPreviewer);
}
