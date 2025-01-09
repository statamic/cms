import axios from 'axios';

const http = axios.create({
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

export default http;
