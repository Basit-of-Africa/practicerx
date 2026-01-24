import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const Dashboard = () => {
    const [showDemoPrompt, setShowDemoPrompt] = useState(false);
    const [importing, setImporting] = useState(false);

    useEffect(() => {
        apiFetch({ path: '/ppms/v1/patients' }).then((data) => {
            if (data.length === 0) setShowDemoPrompt(true);
        });
    }, []);

    const handleImport = () => {
        setImporting(true);
        apiFetch({ path: '/ppms/v1/system/seed', method: 'POST' })
            .then(() => {
                setShowDemoPrompt(false);
                alert('Demo data imported successfully! Refresh the page to see it.');
            })
            .catch((error) => {
                console.error(error);
                alert('Import failed.');
            })
            .finally(() => setImporting(false));
    };

    return (
        <div className="practicerx-page">
            <h1>Dashboard</h1>
            <p>Welcome to PracticeRx.</p>

            {showDemoPrompt && (
                <div className="notice notice-info inline" style={{ padding: '15px', margin: '20px 0' }}>
                    <h3>Get Started Quickly</h3>
                    <p>It looks like this is a fresh installation. Would you like to import some demo data to explore the system?</p>
                    <button
                        className="button button-primary"
                        onClick={handleImport}
                        disabled={importing}
                    >
                        {importing ? 'Importing...' : 'Import Demo Data'}
                    </button>
                </div>
            )}
        </div>
    );
};

export default Dashboard;
