import React, {useEffect, useState} from 'react';


const Settings = ({nonce, urls}) => {
    const [apiKey, setApiKey] = useState('');
    const [errorMessage, setErrorMessage] = useState('');
    const [successMessage, setSuccessMessage] = useState('');
    const [isSaving, setSaving] = useState(false);
    const [isGetting, setGetting] = useState(true);

    const updateApiKey = (event) => setApiKey(event.target.value);
    const updateSettings = async (event) => {
        event.preventDefault();
        setSaving(true);
        await fetch(urls.settings, {
            body: JSON.stringify({apiKey}),
            method: 'POST',
            headers: new Headers({
                'Content-Type': 'application/json',
                'X-WP-Nonce': nonce
            }),
        });

        // check if API key and suite ID are valid
        try {
            setErrorMessage('');
            setSuccessMessage('Settings saved and validated with API!')
        } catch (error) {
            setErrorMessage(error.message);
            setSuccessMessage('')
        }
        setSaving(false)
    };

    useEffect(() => {
        const getSettings = async () => {
            let json = null;
            let elapsed = false;
            setGetting(true);
            // display loading for a minimum amount of time to prevent flashing
            setTimeout(() => {
                elapsed = true;
                if (json) {
                    setGetting(false)
                }
            }, 300);

            const response = await fetch(urls.settings, {
                headers: new Headers({'X-WP-Nonce': nonce})
            });

            json = await response.json();
            setApiKey(json.value.apiKey);
            if (elapsed) {
                setGetting(false)
            }
        };
        getSettings()
    }, [nonce, urls]);

    if (isGetting) {
        return <p>Loading...</p>
    }

    return (
        <div>
            <h1>hoopsAI Plugin Settings</h1>
            <h2>AI-Generated Sports Content</h2>
            {!apiKey && <p><a href="https://dashboard.hoopsai.com" target="_blank" rel="noopener noreferrer"
                              className="button button-primary">Login or sign up to get your API key</a></p>}
            <form onSubmit={updateSettings}>
                <p><label>API Key: <input type="password" value={apiKey} onChange={updateApiKey}/></label></p>
                {errorMessage && <div className="error settings-error"><p>{errorMessage}</p></div>}
                {successMessage && <div className="notice notice-success"><p>{successMessage}</p></div>}
                <p>
                    <button type="submit" className="button button-primary" disabled={isSaving}>Submit</button>
                </p>
            </form>
        </div>
    );
};

export default Settings;
