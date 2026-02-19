import { useState, useContext } from '@wordpress/element';
import { RouterContext } from '../utils/Router';

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
                    <label>First name</label>
                    <input value={firstName} onChange={(e) => setFirstName(e.target.value)} />
                </div>
                <div>
                    <label>Last name</label>
                    <input value={lastName} onChange={(e) => setLastName(e.target.value)} />
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} />
                </div>
                <div>
                    <label>Password</label>
                    <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} />
                </div>
                {error && <div style={{ color: 'red' }}>{error}</div>}
                <button type="submit">Register</button>
            </form>
        </div>
    );
};

export default ClientRegister;
