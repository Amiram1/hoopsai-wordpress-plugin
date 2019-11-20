export const request = async (endpoint, params = {}) => {
    const {urls} = window.hoopsai_wp_ajax;
    const connector = urls.proxy.indexOf('?') > -1 ? '&' : '?'; // check for query param in existing WP URL (depends on permalink settings)

    const response = await fetch(`${urls.proxy}${connector}${new URLSearchParams(Object.entries({
        ...params,
        endpoint
    }))}`);

    const json = await response.json();

    if (json.code === 200) {
        return json
    } else {
        throw new Error(json.message)
    }
};


export const wpRequest = async (postDict) => {
    const {nonce, urls} = window.hoopsai_wp_ajax;

    const response = await fetch(urls.posts, {
        body: JSON.stringify(postDict),
        method: 'POST',
        headers: new Headers({
            'Content-Type': 'application/json',
            'X-WP-Nonce': nonce
        }),
    });

    const json = await response.json();

    if (json.code === 200 || json.code === 204) {
        return json
    } else {
        throw new Error(json.message)
    }
};

export const wpGetCategories = async () => {
    const {nonce, urls} = window.hoopsai_wp_ajax;

    const response = await fetch(urls['get-categories'], {
        method: 'GET',
        headers: new Headers({
            'X-WP-Nonce': nonce
        }),
    });

    const json = await response.json();

    if (json.code === 200) {
        return json
    } else {
        throw new Error(json.message)
    }
};


export const createPost = async (postDict) => {return wpRequest(postDict)};
export const getCategories = async () => {return wpGetCategories()};
export const getDailyPreviews = async (year, month, day) => request(`/api/v1/resources/nba/daily/preview/${year}/${month}/${day}`);
export const getDailyRecaps = async (year, month, day) => request(`/api/v1/resources/nba/daily/recap/${year}/${month}/${day}`);
