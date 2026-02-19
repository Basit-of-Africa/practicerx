import { useState } from '@wordpress/element';
import ClientTopNav from './ClientTopNav';
import ClientSidebar from './ClientSidebar';

const ClientLayout = ({ children }) => {
    const [collapsed, setCollapsed] = useState(false);

    const toggle = () => setCollapsed((s) => !s);

    return (
        <div className="client-layout">
            <ClientSidebar collapsed={collapsed} />
            <div className={"client-content" + (collapsed ? ' collapsed' : '')} style={{ minHeight: '100vh' }}>
                <div className="client-topnav"><ClientTopNav onToggle={toggle} collapsed={collapsed} /></div>
                <main style={{ padding: 20 }}>{children}</main>
            </div>
        </div>
    );
};

export default ClientLayout;
