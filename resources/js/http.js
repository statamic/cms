import axios from "axios";

const http = axios.create({
    // baseURL: `your API end point`,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

window.axios = axios;

export default http;
