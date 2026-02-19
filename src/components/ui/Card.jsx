import React from 'react';

const Card = ({ children, className = '', ...props }) => {
    return (
        <div className={`bg-white border border-gray-100 rounded-md shadow-sm p-4 ${className}`} {...props}>
            {children}
        </div>
    );
};

export default Card;
