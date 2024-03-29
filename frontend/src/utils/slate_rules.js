import React from 'react';

const BLOCK_TAGS = {
    blockquote: 'quote',
    p: 'paragraph',
    pre: 'code',
};

// Add a dictionary of mark tags.
const MARK_TAGS = {
    em: 'italic',
    strong: 'bold',
    u: 'underline',
};

export const rules = [
    {
        deserialize(el, next) {
            const type = BLOCK_TAGS[el.tagName.toLowerCase()];
            if (type) {
                return {
                    object: 'block',
                    type: type,
                    data: {
                        className: el.getAttribute('class'),
                    },
                    nodes: next(el.childNodes),
                }
            }
        },
        serialize(obj, children) {
            if (obj.object === 'block') {
                switch (obj.type) {
                    case 'title':
                        return <h2>{children}</h2>;
                    case 'code':
                        return (<pre><code>{children}</code></pre>);
                    case 'paragraph':
                        return <p>{children}</p>;
                    case 'quote':
                        return <blockquote>{children}</blockquote>;
                    default:
                        return <p>{children}</p>;
                }
            }
        },
    },
    // Add a new rule that handles marks...
    {
        deserialize(el, next) {
            const type = MARK_TAGS[el.tagName.toLowerCase()];
            if (type) {
                return {
                    object: 'mark',
                    type: type,
                    nodes: next(el.childNodes),
                }
            }
        },
        serialize(obj, children) {
            if (obj.object === 'mark') {
                switch (obj.type) {
                    case 'bold':
                        return <strong>{children}</strong>;
                    case 'italic':
                        return <em>{children}</em>;
                    case 'underline':
                        return <u>{children}</u>;
                    default:
                        return <p>{children}</p>;
                }
            }
        },
    },
    // Add a new rule that handles marks...
    {
        deserialize(el, next) {
            const type = MARK_TAGS[el.tagName.toLowerCase()];
            if (type) {
                return {
                    object: 'inline',
                    type: type,
                    nodes: next(el.childNodes),
                }
            }
        },
        serialize(obj, children) {
            if (obj.object === 'inline') {
                switch (obj.type) {
                    case 'paragraph':
                        return <p>{children}</p>;
                    case 'link': {
                        const {data} = obj;
                        const href = data['href'];
                        return (
                            <a target="_blank"  rel="noopener noreferrer" href={href}>
                                {children}
                            </a>
                        )
                    }
                    case 'image': {
                        const src = obj.data['src'];
                        return (
                            <img
                                alt={''}
                                src={src}
                                className='hoopsai_img'
                            />
                        )
                    }
                    case 'video':
                        return;
                    default:
                        return
                }
            }
        },
    },
];
