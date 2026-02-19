import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { Card } from '../components/ui';
import { Button } from '../components/ui';

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
        <div className="practicerx-page px-portal">
            <h1 className="text-2xl font-semibold mb-4">Dashboard</h1>
            <p className="mb-6">Welcome to PracticeRx.</p>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '16px' }}>
                <Card>
                    <h3 className="text-lg font-medium">Upcoming Appointments</h3>
                    <p className="text-sm text-muted">You have 3 appointments this week.</p>
                    <div style={{ marginTop: '12px' }}>
                        <Button onClick={() => window.location.hash = '#/appointments'}>View Appointments</Button>
                    </div>
                </Card>

                <Card>
                    <h3 className="text-lg font-medium">Clients</h3>
                    <p className="text-sm text-muted">Manage your client list and records.</p>
                    <div style={{ marginTop: '12px' }}>
                        <Button variant="ghost" onClick={() => window.location.hash = '#/patients'}>Manage Clients</Button>
                    </div>
                </Card>

                <Card>
                    <h3 className="text-lg font-medium">Get Started</h3>
                    <p className="text-sm text-muted">Import demo data to explore all features.</p>
                    <div style={{ marginTop: '12px' }}>
                        <Button onClick={handleImport} disabled={importing}>{importing ? 'Importing...' : 'Import Demo Data'}</Button>
                    </div>
                </Card>
            </div>

        </div>
    );
};

export default Dashboard;
