import React from 'react';
import ReactDOM from 'react-dom';
import Dashboard from './Dashboard';
import Settings from './Settings';

const {urls, nonce} = window.hoopsai_wp_ajax;
const dashboardContainer = document.getElementById('hoopsai_wp_dashboard');
const settingsContainer = document.getElementById('hoopsai_wp_settings');

if (dashboardContainer) {
    ReactDOM.render(<Dashboard/>, dashboardContainer);
}

if (settingsContainer) {
    ReactDOM.render(<Settings nonce={nonce} urls={urls}/>, settingsContainer);
}
