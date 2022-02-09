/* eslint-disable @nextcloud/no-deprecations */
import termsAgreementHandler from "./imports/TermsAgreementHandler"

$(async () => {
    console.log('Terms agreement script loaded');
    $('#termsAgreementForm').on('change', 'input[type=checkbox]', function() {
        handleChange(this);
        console.log(this.name+' '+this.value+' '+this.checked);
    });
    if(await termsAgreementHandler.setup().getAgreementStatus("1.2.8") != true)
    {
        showAgreement();
    }
    else{
        hideAgreement();
    }

})
async function handleChange(checkbox) {
    if(checkbox.checked == true){
        if(await termsAgreementHandler.setup().setAgreed("1.2.8"))
        {
            console.log('Agreement is fixed.')
            hideAgreement();
        }
        else{
            console.log('Agreement has failed.')
            showAgreement();
        }
    }else{
        console.log('Agreement is unchecked.')

       showAgreement();
   }
}

function showAgreement()
{
    $(".TermsAgreement").removeClass("hidden").addClass("shown");
    $(".Settingspage").removeClass("shown").addClass("hidden");
}
function hideAgreement()
{
    $(".TermsAgreement").removeClass("shown").addClass("hidden");
    $(".Settingspage").removeClass("hidden").addClass("shown");
}
