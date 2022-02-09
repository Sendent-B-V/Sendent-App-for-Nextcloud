/* eslint-disable @nextcloud/no-deprecations */
import axios from '@nextcloud/axios';
import { generateUrl } from '@nextcloud/router';

type TermsAgreementItem = {
    Version: string
    Agreed: string,
}

export default class TermsAgreementHandler {
    private static instance: TermsAgreementHandler;

    // eslint-disable-next-line @typescript-eslint/no-empty-function
    private constructor() {

    }

    public async getAgreementStatus(version): Promise<boolean> {
        const { data: status } = await this.isAgreed(version);
        if(status == null)
        {
            console.log("getAgreementStatus == is null")
            return false;
        }
        else if(status.Agreed == "yes")
        {
            console.log("getAgreementStatus == yes")
            return true;
        }
        else
        {
            console.log("getAgreementStatus == is invalid")
            return false;
        }
    }
    public async setAgreed(version): Promise<boolean> {
        const { data: status } = await this.agreed(version);
        if(status != null && status.Agreed == "yes")
        {
            console.log("setAgreed == yes")
            return true;
        }
        else
        {
            console.log("setAgreed == is invalid")
            return false;
        }
    }
    public static setup(): TermsAgreementHandler {
        if (!this.instance) {
            this.instance = new TermsAgreementHandler();
        }
        return this.instance;
    }


    private isAgreed(version) {
        const url = generateUrl('/apps/sendent/api/1.0/termsagreement/isagreed/' + version);
        return axios.get<TermsAgreementItem>(url);
    }
    private agreed(version) {
        const url = generateUrl('/apps/sendent/api/1.0/termsagreement/agree/' + version);
        return axios.get<TermsAgreementItem>(url);
    }
}
