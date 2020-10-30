const {Component, Mixin} = Shopware;

import template from './sw-plugin-config.html.twig';

Component.override('sw-plugin-config', {
    template,
    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('sw-inline-snippet')
    ],

    inject: ['CoinpaymentsPaymentConfigService'],

    data() {
        return {
            config: {},
            isLoading: false,
            isTesting: false,
            isSaveSuccessful: false,
            isTestSuccessful: false,
            clientIdFilled: false,
            clientSecretFilled: false,
            showValidationErrors: false,
            isSupportModalOpen: false,
        }
    },

    computed: {
        credentialsMissing: function () {
            return !this.clientIdFilled || !this.clientSecretFilled;
        }
    },

    methods: {

        saveFinish() {
            this.isSaveSuccessful = false;
        },

        onCoinTest() {
            this.isTesting = true;
            this.isTestSuccessful = false;


            this.isLoading = true;
            if (this.credentialsMissing) {
                this.showValidationErrors = true;
                this.isTesting = false;
                return;
            }

            let credentials = {
                clientId: this.getConfigValue('clientId'),
                webhooks: this.getConfigValue('webhooks'),
                clientSecret: this.getConfigValue('clientSecret'),
            };


            this.CoinpaymentsPaymentConfigService.validateApiCredentials(credentials).then((response) => {
                const credentialsValid = response.credentialsValid;
                const error = response.error;

                if (credentialsValid) {
                    this.createNotificationSuccess({
                        title: this.$tc('coinpayments-payment.configForm.messages.titleSuccess'),
                        message: this.$tc('coinpayments-payment.configForm.messages.messageTestSuccess')
                    });
                    this.isTestSuccessful = true;
                } else {
                    this.createNotificationError({
                        title: this.$tc('coinpayments-payment.configForm.messages.titleError'),
                        message: this.$tc('coinpayments-payment.configForm.messages.messageTestError')
                    });
                }
                this.isTesting = false;
            }).catch((errorResponse) => {
                this.createNotificationError({
                    title: this.$tc('coinpayments-payment.configForm.messages.titleError'),
                    message: this.$tc('coinpayments-payment.configForm.messages.messageTestErrorGeneral')
                });
                this.isTesting = false;
            });
        },

        onConfigChange(config) {
            this.config = config;

            this.checkCredentialsFilled();

            this.showValidationErrors = false;
        },

        getBind(element, config) {

            if (this.domain == 'CoinPayments.config') {
                if (config !== this.config) {
                    this.onConfigChange(config);
                }

                if (this.showValidationErrors) {
                    if (element.name === 'CoinPayments.config.clientId' && !this.clientIdFilled) {
                        element.config.error = {
                            code: 1,
                            detail: this.$tc('coinpayments-payment.configForm.messages.messageNotBlank')
                        };
                    }
                    if (element.name === 'CoinPayments.config.clientSecret' && !this.clientSecretFilled) {
                        element.config.error = {
                            code: 1,
                            detail: this.$tc('coinpayments-payment.configForm.messages.messageNotBlank')
                        };
                    }
                }
            }

            return element;
        },

        checkCredentialsFilled() {
            this.clientIdFilled = !!this.getConfigValue('clientId');

            if (!this.getConfigValue('webhooks')) {
                this.clientSecretFilled = true;
            } else {
                this.clientSecretFilled = !!this.getConfigValue('clientSecret');
            }
        },

        getConfigValue(field) {
            const defaultConfig = this.$refs.systemConfig.actualConfigData.null;
            const salesChannelId = this.$refs.systemConfig.currentSalesChannelId;

            if (salesChannelId === null) {
                return this.config[`CoinPayments.config.${field}`];
            }

            return this.config[`CoinPayments.config.${field}`]
                || defaultConfig[`CoinPayments.config.${field}`];
        },
    }
});
