import React, {useState} from 'react';
import {getDailyRecaps, createPost, getDailyPreviews} from './api';
import Html from 'slate-html-serializer'
import {rules} from './utils/slate_rules'
import './dashboard.css'
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";

const ErrorMessage = ({message}) => (
    <span>
    {message} Please try updating your <a href="options-general.php?page=hoopsai-wp-settings">settings</a>.
  </span>
);

const Dashboard = () => {
    const [errorMessage, setErrorMessage] = useState('');
    const [startDate, setStartDate] = useState(new Date());
    const [author, setAuthor] = useState('');
    const [tags, setTags] = useState('');
    const [categories, setCategories] = useState('');
    const [autoPublish, setAutoPublish] = useState(true);
    const [autoTags, setAutoTags] = useState(true);
    const [allowOverride, setAllowOverride] = useState(true);
    const [recapsState, setRecapsState] = useState({});
    const [previewsState, setPreviewsState] = useState({});

    const handleCreateDailyRecaps = async () => {
        const content = await getDailyRecaps(startDate.getFullYear(), startDate.getMonth() + 1, startDate.getDate());
        handleCreatePosts(content, 'recap')
    };

    const handleCreateDailyPreviews = async () => {
        const content = await getDailyPreviews(startDate.getFullYear(), startDate.getMonth() + 1, startDate.getDate());
        handleCreatePosts(content, 'preview')
    };

    const handleCreatePosts = (content, contentType) => {
        const postArr = [];
        const stateDict = {};
        const html = new Html({rules});

        content.result.games.forEach((game) => {
            stateDict[game['game_id']] = {
                gameId: game['game_id'],
                gameStatus: game['game_status'],
                awayTeam: game['away']['team_abbrev'],
                homeTeam: game['home']['team_abbrev'],
                utc_game_date: game['utc_game_date'],
                contentAvailable: game[contentType].hrtf !== null
            };

            const targetTagsArr = tags.replace(/ /g, '').split(',');

            const targetCategoriesArr = categories.replace(/ /g, '').split(',');

            if (autoTags) {
                targetTagsArr.push(game['away']['team_abbrev'], game['home']['team_abbrev']);
            }

            if (game[contentType].hrtf !== null) {
                const html_content = html.serialize(JSON.parse(game[contentType].hrtf.toString('utf-8')));
                const html_header = html_content.split("</h2>")[0];
                const html_body = html_content.split("</h2>")[1];

                const postDict = {
                    gameId: game['game_id'],
                    postStatus: (autoPublish) ? 'publish' : 'draft',
                    postTitle: html_header.split("<h2>")[1],
                    postContent: html_body,
                    postAuthor: author,
                    postTags: targetTagsArr,
                    postCategories: targetCategoriesArr,
                };
                postArr.push(postDict);
            }
        });

        try {
            Promise.all(
                postArr.map(async postDict => {
                    const res = await createPost(postDict);
                    if (res.code === 200) {
                        return postDict.gameId
                    }
                })
            ).then((data) => {
                data.forEach((newPosts) => {
                    stateDict[newPosts].postCreated = true
                });

                if (contentType === 'recap') {
                    setRecapsState(stateDict)
                } else {
                    setPreviewsState(stateDict)
                }
            });
        } catch (error) {
            setErrorMessage(<ErrorMessage message={error.message}/>)
        }
    };

    if (errorMessage) {
        return <div>{errorMessage}</div>
    }

    const getContentStatus = (targetContent) => {
        return Object.keys(targetContent).map((gameId) => (
            <tr key={targetContent[gameId].gameId}>
                <th>{targetContent[gameId].gameId}</th>
                <th>{targetContent[gameId].gameStatus}</th>
                <th>{targetContent[gameId].awayTeam}</th>
                <th>{targetContent[gameId].homeTeam}</th>
                <th>{targetContent[gameId].utc_game_date}</th>
                <th>{(targetContent[gameId].contentAvailable) ? 'yes' : 'no'}</th>
            </tr>));
    };

    return (
        <div className="hoopsai_wp_wrapper">
            <div className="hoopsai_main">
                <h1>hoopsAI Content Generation Menu</h1>
                <h2> Generate Content for Selected Date - {startDate.toLocaleDateString("en-US")}</h2>

                <div>
                    <h2>
                        Choose Date:
                    </h2>
                    <DatePicker selected={startDate} onChange={(date) => setStartDate(date)}/>
                </div>

                <div className="hoopsai_content">
                    <div className="hoopsai_previews">
                        <h2>Daily Content</h2>

                        <label className="hoopsai_label">
                            Overwrite Existing Games:
                            <input className="hoopsai_input" type="checkbox" checked={allowOverride}
                                   onChange={(e) => setAllowOverride(e.target.checked)}/>
                        </label>

                        <label className="hoopsai_label">
                            Automatic Publish:
                            <input className="hoopsai_input" type="checkbox" checked={autoPublish}
                                   onChange={(e) => setAutoPublish(e.target.checked)}/>
                        </label>

                        <label className="hoopsai_label">
                            Automatic Tags:
                            <input className="hoopsai_input" type="checkbox" checked={autoTags} onChange={(e) => setAutoTags(e.target.checked)}/>
                        </label>

                        <label className="hoopsai_label">
                            Author: {author}
                            <input className="hoopsai_input" type="text" value={author} onChange={(e) => setAuthor(e.target.value)}/>
                        </label>

                        <label className="hoopsai_label">
                            Tags Array: {tags}
                            <input className="hoopsai_input" type="text" value={tags} onChange={(e) => setTags(e.target.value)}/>
                        </label>

                        <label className="hoopsai_label">
                            Categories: {categories}
                            <input className="hoopsai_input" type="text" value={categories} onChange={(e) => setCategories(e.target.value)}/>
                        </label>

                        <h3>Generate Daily Previews</h3>

                        <button type="button" className="button button-primary" onClick={handleCreateDailyPreviews}>
                            Generate
                        </button>

                        <h3>Generate Daily Recaps</h3>

                        <button type="button" className="button button-primary" onClick={handleCreateDailyRecaps}>
                            Generate
                        </button>
                    </div>

                    <div className="hoopsai_status">
                        <h2>Recaps Status</h2>
                        <table>
                            <thead>
                            <tr>
                                <th>Game ID</th>
                                <th>Game Status</th>
                                <th>Away Team</th>
                                <th>Home Team</th>
                                <th>UTC Game Date</th>
                                <th>Content Available</th>
                            </tr>
                            </thead>
                            <tbody>
                            {getContentStatus(recapsState)}
                            </tbody>
                        </table>
                        <h2>Previews Status</h2>
                        <table>
                            <thead>
                            <tr>
                                <th>Game ID</th>
                                <th>Game Status</th>
                                <th>Away Team</th>
                                <th>Home Team</th>
                                <th>UTC Game Date</th>
                                <th>Content Available</th>
                            </tr>
                            </thead>
                            <tbody>
                            {getContentStatus(previewsState)}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Dashboard;
