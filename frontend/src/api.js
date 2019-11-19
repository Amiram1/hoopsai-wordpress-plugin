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


export const getSchedule = async (year, month, day) => request(`/api/v1/resources/nba/schedule/${year}/${month}/${day}`);
export const createPost = async (postDict) => {return wpRequest(postDict)};
export const getDailyPreviews = async (year, month, day) => request(`/api/v1/resources/nba/daily/preview/${year}/${month}/${day}`);
export const getDailyRecaps = async (year, month, day) => request(`/api/v1/resources/nba/daily/recap/${year}/${month}/${day}`);

//
//
// export const getMatchup = async (home_team, away_team, gameId) => {
//     const API = '/api/v1/resources/nba/matchups';
//     const options = getOptions();
//
//     let params = '';
//
//     if (gameId) {
//         params = `?game_id=${gameId}`
//     } else {
//         params = `?team_id=${home_team}&opponent_id=${away_team}`
//     }
//
//     try {
//         const response = await fetch(`${ENDPOINT}${API}${params}`, options);
//
//         if (!response.ok) {
//             throw Error(response.statusText);
//         }
//
//         const json = await response.json();
//
//         return json.result["matchup"]
//     } catch (error) {
//         console.log(error);
//     }
// };
//
// export const getJournal = async (journalType, gameId) => {
//     if (journalType !== 'recap' && journalType !== 'preview') {
//         throw Error('validation error');
//     }
//
//     const API = `/api/v1/resources/nba/journal/${journalType}`;
//     const options = getOptions();
//
//     try {
//         const response = await fetch(`${ENDPOINT}${API}/${gameId}`, options);
//
//         if (!response.ok) {
//             throw Error(response.statusText);
//         }
//
//         const json = await response.json();
//         return {
//             text: json.result["game"],
//             content: JSON.parse(json.result["game"].toString('utf-8')),
//             properties: JSON.parse(json.result["properties"].toString('utf-8')),
//         }
//     } catch (error) {
//         console.log(error);
//     }
// };
//
