const ClientTopNav = ({ onToggle, collapsed }) => {
    return (
        <div className="nav-inner" style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <button aria-label="Toggle sidebar" onClick={onToggle} style={{ fontSize: 18 }}>☰</button>
                <div className="brand">PracticeRx</div>
            </div>
            <div className="nav-actions">
                <div style={{ color: '#6b7280' }}>Welcome</div>
                <div>
                    <button onClick={() => window.location.hash = '#/client/profile'} style={{ color: '#0073aa', textDecoration: 'none', background: 'transparent', border: 'none', cursor: 'pointer' }} aria-label="Profile">Profile</button>
                </div>
            </div>
        </div>
    );
};

export default ClientTopNav;
