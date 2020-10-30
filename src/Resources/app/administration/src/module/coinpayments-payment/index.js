const {Module} = Shopware;
import './extension/sw-plugin-config';

import enGB from './snippet/en_GB.json';

Module.register('coin-plugin', {
    type: 'plugin',
    name: 'CoinPayments',
    title: 'coinpayments-payment.module.title',
    description: 'coinpayments-payment.module.description',
    version: '2.0.0',
    targetVersion: '2.0.0',
    color: '#333',
    icon: 'default-action-settings',
    snippets: {
        'en-GB': enGB,
    },
    routes: {
        index: {
            component: 'coin-payment-config'
        }
    }
});
