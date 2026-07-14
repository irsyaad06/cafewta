import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let tabId = sessionStorage.getItem('tab_id');
if (tabId) {
    window.axios.defaults.headers.common['X-Tab-Id'] = tabId;
}
