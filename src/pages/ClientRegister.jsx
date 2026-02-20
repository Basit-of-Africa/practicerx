import { useState, useContext } from '@wordpress/element';
import { RouterContext } from '../utils/Router';
import { Input, Label } from '../components/ui';

const ClientRegister = () => {
    const [email, setEmail] = useState('');
    const [firstName, setFirstName] = useState('');
    const [lastName, setLastName] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const { navigate } = useContext(RouterContext);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        try {
            const res = await fetch(practicerxSettings.root + 'auth/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email: email, first_name: firstName, last_name: lastName, password: password })
            });
            const data = await res.json();
            if (!res.ok) {
                setError(data.message || 'Registration failed');
                return;
            }
            localStorage.setItem('ppms_token', data.token);
            navigate('/client');
        } catch (err) {
            setError('Network error');
        }
    };

    return (
        <div style={{ padding: 20 }}>
            <h2>Client Register</h2>
            <form onSubmit={handleSubmit}>
                <div>
                    <Label htmlFor="reg-first">First name</Label>
                    <Input id="reg-first" name="first_name" value={firstName} onChange={(e) => setFirstName(e.target.value)} />
                </div>
                <div>
                    <Label htmlFor="reg-last">Last name</Label>
                    <Input id="reg-last" name="last_name" value={lastName} onChange={(e) => setLastName(e.target.value)} />
                </div>
                <div>
                    <Label htmlFor="reg-email">Email</Label>
                    <Input id="reg-email" name="email" type="email" value={email} onChange={(e) => setEmail(e.target.value)} />
                </div>
                <div>
                    <Label htmlFor="reg-password">Password</Label>
                    <Input id="reg-password" name="password" type="password" value={password} onChange={(e) => setPassword(e.target.value)} />
                </div>
                {error && <div style={{ color: 'red' }}>{error}</div>}
                <button type="submit">Register</button>
            </form>
        </div>
    );
};

export default ClientRegister;
