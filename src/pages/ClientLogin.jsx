import { useState } from '@wordpress/element';
import { useContext } from '@wordpress/element';
import { RouterContext } from '../utils/Router';
import { Input, Label } from '../components/ui';

const ClientLogin = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const { navigate } = useContext(RouterContext);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        try {
            const res = await fetch(practicerxSettings.root + 'auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });
            const data = await res.json();
            if (!res.ok) {
                setError(data.message || 'Login failed');
                return;
            }
            // Save token and redirect
            localStorage.setItem('ppms_token', data.token);
            navigate('/client');
        } catch (err) {
            setError('Network error');
        }
    };

    return (
        <div style={{ padding: 20 }}>
            <h2>Client Login</h2>
            <form onSubmit={handleSubmit}>
                <div>
                    <Label htmlFor="login-email">Email</Label>
                    <Input id="login-email" name="email" type="email" value={email} onChange={(e) => setEmail(e.target.value)} />
                </div>
                <div>
                    <Label htmlFor="login-password">Password</Label>
                    <Input id="login-password" name="password" type="password" value={password} onChange={(e) => setPassword(e.target.value)} />
                </div>
                {error && <div style={{ color: 'red' }}>{error}</div>}
                <button type="submit">Login</button>
            </form>
        </div>
    );
};

export default ClientLogin;
