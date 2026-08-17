import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Kirim CSRF token otomatis dari cookie ke setiap request
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;

let tabId = sessionStorage.getItem('tab_id');
if (tabId) {
    window.axios.defaults.headers.common['X-Tab-Id'] = tabId;
}
