import { Link } from '../utils/Router';

const Layout = ({ children }) => {
    return (
        <div className="practicerx-layout" style={{ display: 'flex', minHeight: '100vh' }}>
            <div className="practicerx-sidebar" style={{ width: '200px', background: '#fff', borderRight: '1px solid #ddd', padding: '20px' }}>
                <h2>PracticeRx</h2>
                <nav>
                    <ul style={{ listStyle: 'none', padding: 0 }}>
                        <li style={{ marginBottom: '10px' }}><Link to="/">Dashboard</Link></li>
                        <li style={{ marginBottom: '10px' }}><Link to="/appointments">Appointments</Link></li>
                        <li style={{ marginBottom: '10px' }}><Link to="/patients">Clients</Link></li>
                        <li style={{ marginBottom: '10px' }}><Link to="/telehealth">Telehealth</Link></li>
                        <li style={{ marginBottom: '10px' }}><Link to="/campaigns">Campaigns</Link></li>
                        <li style={{ marginBottom: '10px' }}><Link to="/recipes">Recipes</Link></li>
                        <li style={{ marginBottom: '10px' }}><Link to="/meal-plans">Meal Plans</Link></li>
                        <li style={{ marginBottom: '10px' }}><Link to="/forms">Forms</Link></li>
                        <li style={{ marginBottom: '10px' }}><Link to="/documents">Documents</Link></li>
                        <li style={{ marginBottom: '10px' }}><Link to="/health-tracking">Health Tracking</Link></li>
                        <li style={{ marginBottom: '10px' }}><Link to="/settings">Settings</Link></li>
                    </ul>
                </nav>
            </div>
            <div className="practicerx-content" style={{ flex: 1, padding: '20px' }}>
                {children}
            </div>
        </div>
    );
};

export default Layout;
