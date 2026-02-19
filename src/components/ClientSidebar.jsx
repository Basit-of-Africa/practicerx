import { Link } from '../utils/Router';

const ClientSidebar = ({ collapsed = false }) => {
    return (
        <aside className={"client-sidebar" + (collapsed ? ' collapsed' : '')} style={{ padding: 20 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 20 }}>
                <div style={{ width: 36, height: 36, borderRadius: 8, background: '#e6f0f5' }} />
                {!collapsed && <h3 style={{ margin: 0 }}>PracticeRx</h3>}
            </div>
            <nav>
                <ul style={{ listStyle: 'none', padding: 0 }}>
                    <li style={{ marginBottom: 10 }}><Link to="/client">{!collapsed ? 'Dashboard' : 'Dash'}</Link></li>
                    <li style={{ marginBottom: 10 }}><Link to="/client/appointments">{!collapsed ? 'Appointments' : 'Appts'}</Link></li>
                    <li style={{ marginBottom: 10 }}><Link to="/client/documents">{!collapsed ? 'Documents' : 'Docs'}</Link></li>
                    <li style={{ marginBottom: 10 }}><Link to="/client/billing">{!collapsed ? 'Billing' : 'Bill'}</Link></li>
                    <li style={{ marginTop: 20 }}><Link to="/">{!collapsed ? 'Back to Admin' : 'Admin'}</Link></li>
                </ul>
            </nav>
        </aside>
    );
};

export default ClientSidebar;
