<?php

namespace Troopers\MangopayBundle;

final class TroopersMangopayEvents
{
    /**
     * The NEW_USER event occurs when a user is created.
     */
    const NEW_USER = 'troopers_mangopay.user.new';

    /**
     * The NEW_USER_LEGAL event occurs when a user legal is created.
     */
    const NEW_USER_LEGAL = 'troopers_mangopay.user_legal.new';

    /**
     * The NEW_WALLET event occurs when a wallet is created.
     */
    const NEW_WALLET = 'troopers_mangopay.wallet.new';

    /**
     * The UPDATE_WALLET event occurs when a wallet is updated.
     */
    const UPDATE_WALLET = 'troopers_mangopay.wallet.update';

    /**
     * The NEW_WALLET_FOR_USER event occurs when a wallet is created for a User.
     */
    const NEW_WALLET_FOR_USER = 'troopers_mangopay.wallet.new_for_user';

    /**
     * The NEW_KYCPAGE event occurs when a kyc page is created.
     */
    const NEW_KYCPAGE = 'troopers_mangopay.kyc_page.new';

    /**
     * The NEW_KYCDOCUMENT event occurs when a kyc document is created.
     */
    const NEW_KYCDOCUMENT = 'troopers_mangopay.kyc_document.new';

    /**
     * The NEW_KYCDOCUMENT_FOR_USER event occurs when a kyc document is created for a User.
     */
    const NEW_KYCDOCUMENT_FOR_USER = 'troopers_mangopay.kyc_document.new_for_user';

    /**
     * The ASK_VALIDATION_KYCDOCUMENT event occurs when a kyc document validation is asked.
     */
    const ASK_VALIDATION_KYCDOCUMENT = 'troopers_mangopay.kyc_document.ask_validation';

    /**
     * The NEW_BANKINFORMATION event occurs when a bank information is created.
     */
    const NEW_BANKINFORMATION = 'troopers_mangopay.bank_information.new';

    /**
     * The UPDATE_BANKINFORMATION event occurs when a bank information is updated.
     */
    const UPDATE_BANKINFORMATION = 'troopers_mangopay.bank_information.update';

    /**
     * The DISABLE_BANKINFORMATION event occurs when a bank information is deleted.
     */
    const DISABLE_BANKINFORMATION = 'troopers_mangopay.bank_information.disabled';

    /**
     * The NEW_BANKINFORMATION_FOR_USER event occurs when a bank information is created for a User.
     */
    const NEW_BANKINFORMATION_FOR_USER = 'troopers_mangopay.bank_information.new_for_user';

    /**
     * The NEW_CARD_PREAUTHORISATION event occurs when a card preauthorisation is created.
     */
    const NEW_CARD_PREAUTHORISATION = 'troopers_mangopay.card.preauthorisation.new';

    /**
     * The UPDATE_CARD_PREAUTHORISATION event occurs when a card preauthorisation is updated.
     */
    const UPDATE_CARD_PREAUTHORISATION = 'troopers_mangopay.card.preauthorisation.update';

    /**
     * The CANCEL_CARD_PREAUTHORISATION event occurs when a card preauthorisation is canceled.
     */
    const CANCEL_CARD_PREAUTHORISATION = 'troopers_mangopay.card.preauthorisation.cancel';

    /**
     * The NEW_CARD_REGISTRATION event occurs when a card registration is created.
     */
    const NEW_CARD_REGISTRATION = 'troopers_mangopay.card.registration.new';

    /**
     * The UPDATE_CARD_REGISTRATION event occurs when a card registration is updated.
     */
    const UPDATE_CARD_REGISTRATION = 'troopers_mangopay.card.registration.update';

    /**
     * The NEW_PAY_IN event occurs when a payin is created.
     */
    const NEW_PAY_IN = 'troopers_mangopay.pay_in.new';

    /**
     * The NEW_BANK_WIRE_PAY_IN event occurs when a bankwire payin is created.
     */
    const NEW_BANK_WIRE_PAY_IN = 'troopers_mangopay.ban_wire_pay_in.new';

    /**
     * The ERROR_PAY_IN event occurs when a payin is errored.
     */
    const ERROR_PAY_IN = 'troopers_mangopay.pay_in.error';

    /**
     * The ERROR_BANK_WIRE_PAY_IN event occurs when a bankwire payin is errored.
     */
    const ERROR_BANK_WIRE_PAY_IN = 'troopers_mangopay.ban_wire_pay_in.error';
}
