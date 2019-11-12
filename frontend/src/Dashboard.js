import React, {useState} from 'react';
import {getJournal, createPost} from './api';
import './dashboard.css'


const ErrorMessage = ({message}) => (
    <span>
    {message} Please try updating your <a href="options-general.php?page=hoopsai-wp-settings">settings</a>.
  </span>
);

const Dashboard = () => {
    const [errorMessage, setErrorMessage] = useState('');

    const getContent = async () => {
        const content = await getJournal();
        return {
            text: content.result["text"],
            content: JSON.parse(content.result["game"].toString('utf-8')),
            properties: JSON.parse(content.result["properties"].toString('utf-8')),
        };
    };

    const handleCreatePost = async () => {
        const content = await getContent();

        const postDict = {
            postTitle: 'test',
            postContent: content.text,
        };

        try {
            const res = await createPost(postDict);
        } catch (error) {
            setErrorMessage(<ErrorMessage message={error.message}/>)
        }
    };

    if (errorMessage) {
        return <div>{errorMessage}</div>
    }

    return (
        <div className="hoopsai_wp_wrapper">
            <p>
                <button type="button" className="button button-primary" onClick={handleCreatePost}>
                    Generate Daily Previews
                </button>
            </p>
        </div>
    );
};

export default Dashboard;
