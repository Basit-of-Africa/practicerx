const ClientHeader = () => {
    return (
        <header style={{ background: '#fff', borderBottom: '1px solid #eee', padding: 12 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <div style={{ fontWeight: 600 }}>Client Portal</div>
                <div>
                    <button onClick={() => alert('Logout (demo)')} style={{ color: '#0073aa', textDecoration: 'none', background: 'transparent', border: 'none', cursor: 'pointer' }} aria-label="Logout">Logout</button>
                </div>
            </div>
        </header>
    );
};

export default ClientHeader;
