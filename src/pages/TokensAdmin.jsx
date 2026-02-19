import { useState, useEffect } from '@wordpress/element';
import { Link } from '../utils/Router';

const TokensAdmin = () => {
    const [tokens, setTokens] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const getAuthHeader = () => {
        // Admin is authenticated via WP cookie; we do not need token header here.
        return {};
    };

    const fetchTokens = async () => {
        setLoading(true);
        setError(null);
        try {
            const res = await fetch(practicerxSettings.root + 'auth/tokens', {
                headers: Object.assign({ 'Content-Type': 'application/json' }, getAuthHeader()),
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error('Failed to fetch tokens');
            const data = await res.json();
            setTokens(data);
        } catch (e) {
            setError(e.message);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchTokens();
    }, []);

    const revoke = async (token) => {
        try {
            const res = await fetch(practicerxSettings.root + 'auth/tokens', {
                method: 'POST',
                headers: Object.assign({ 'Content-Type': 'application/json' }, getAuthHeader()),
                credentials: 'same-origin',
                body: JSON.stringify({ token }),
            });
            if (!res.ok) throw new Error('Revoke failed');
            await fetchTokens();
        } catch (e) {
            alert('Error: ' + e.message);
        }
    };

    if (loading) return <div>Loading tokens...</div>;
    if (error) return <div>Error: {error}</div>;

    return (
        <div>
            <h1>Tokens</h1>
            <p>Admin view: list and revoke client tokens.</p>
            {tokens && tokens.length ? (
                <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                    <thead>
                        <tr>
                            <th style={{ textAlign: 'left', padding: '8px' }}>Key</th>
                            <th style={{ textAlign: 'left', padding: '8px' }}>User</th>
                            <th style={{ textAlign: 'left', padding: '8px' }}>Type</th>
                            <th style={{ textAlign: 'left', padding: '8px' }}>Created</th>
                            <th style={{ textAlign: 'left', padding: '8px' }}>Expires</th>
                            <th style={{ textAlign: 'left', padding: '8px' }}>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {tokens.map((t, i) => (
                            <tr key={i} style={{ borderTop: '1px solid #eee' }}>
                                <td style={{ padding: '8px' }}>{t.id}</td>
                                <td style={{ padding: '8px' }}>{t.user_id || '—'}</td>
                                <td style={{ padding: '8px' }}>{t.is_legacy ? 'legacy' : 'token'}</td>
                                <td style={{ padding: '8px' }}>{t.created ? new Date(t.created * 1000).toLocaleString() : '—'}</td>
                                <td style={{ padding: '8px' }}>{t.exp ? new Date(t.exp * 1000).toLocaleString() : '—'}</td>
                                <td style={{ padding: '8px' }}>
                                    <button onClick={() => revoke(t.is_legacy ? t.id : (t.id + '.<secret>'))}>Revoke</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            ) : (
                <div>No tokens found.</div>
            )}
        </div>
    );
};

export default TokensAdmin;
