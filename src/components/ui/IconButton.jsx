import React from 'react';
import Icon from './Icon';

const IconButton = ({ name, onClick, label = '', className = '', size = 16, ...props }) => {
    return (
        <button onClick={onClick} aria-label={label || name} className={`btn ${className}`} style={{ padding: 6 }} {...props}>
            <Icon name={name} width={size} height={size} />
        </button>
    );
};

export default IconButton;
