require(["angular", "../../extension/communication/razorcms/contact-form/js/controller"], function(angular)
{
    angular.module("extension.communication.razorcms.contactForm", ["extension.communication.razorcms.contactForm.controller"]);
    angular.bootstrap(document.querySelector("#communication-razorcms-contact-form"), ["extension.communication.razorcms.contactForm"]);
});