import { Link } from 'react-router-dom';

const Layout = ({ children }) => {
    return (
        <div className="practicerx-layout">
            <div className="practicerx-sidebar">
                <h2>PracticeRx</h2>
                <nav>
                    <ul>
                        <li><Link to="/">Dashboard</Link></li>
                        <li><Link to="/appointments">Appointments</Link></li>
                        <li><Link to="/patients">Patients</Link></li>
                        <li><Link to="/settings">Settings</Link></li>
                    </ul>
                </nav>
            </div>
            <div className="practicerx-content">
                {children}
            </div>
        </div>
    );
};

export default Layout;
