import React from 'react';

const Form = ({ children, onSubmit, className = '' }) => {
    const handle = (e) => {
        e.preventDefault();
        if (onSubmit) onSubmit(new FormData(e.target));
    };

    return (
        <form onSubmit={handle} className={className}>
            {children}
        </form>
    );
};

export default Form;
