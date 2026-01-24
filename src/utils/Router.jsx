import { createContext, useState, useEffect } from '@wordpress/element';

export const RouterContext = createContext({
    path: '/',
    navigate: () => { }
});

export const Router = ({ children }) => {
    const [path, setPath] = useState(window.location.hash.substr(1) || '/');

    useEffect(() => {
        const handleHashChange = () => {
            setPath(window.location.hash.substr(1) || '/');
        };
        window.addEventListener('hashchange', handleHashChange);
        return () => {
            window.removeEventListener('hashchange', handleHashChange);
        };
    }, []);

    const navigate = (newPath) => {
        window.location.hash = newPath;
    };

    return (
        <RouterContext.Provider value={{ path, navigate }}>
            {children}
        </RouterContext.Provider>
    );
};

export const Link = ({ to, children, className }) => {
    return (
        <a
            href={`#${to}`}
            className={className}
            style={{ cursor: 'pointer', color: '#0073aa', textDecoration: 'none' }}
        >
            {children}
        </a>
    );
};
