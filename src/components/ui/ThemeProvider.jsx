import React from 'react';

const ThemeProvider = ({ children }) => {
    return (
        <div className="prx-theme">
            {children}
        </div>
    );
};

export default ThemeProvider;
