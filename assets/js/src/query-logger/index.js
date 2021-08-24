import { render, Fragment, useCallback } from "@wordpress/element";
import { useSelect, useDispatch, AsyncModeProvider, dispatch } from "@wordpress/data";
import styled from "styled-components";
import "./store";

const Drawer = styled.div`
    position: fixed;
    bottom: 0px;
    width: 100%;
    height: 200px;
    z-index: 10000 !important;
    display: flex;
    flex-direction: column;
    background-color: white;
`;

const Bar = styled.div`
    border-top: 1px solid black;
    border-bottom: 1px solid black;
    width: 100%;
    height: 24px;
    display: flex;
    align-items: center;
    background-color: #ccd0d4;

    button {
        padding: 0;
    }

    span.title {
        margin-left: 4px;
        flex-grow: 1;
    }
`;

const adminBar = document.getElementById( "wp-admin-bar-yoast-query-logger" );
if ( adminBar ) {
    adminBar.querySelector( ".ab-item" ).onclick = ( e ) => {
        e.preventDefault();
        dispatch( "yoast/query-logger" ).toggleDrawer();
    };
}

const Menu = () => {
    const { closeDrawer } = useDispatch( "yoast/query-logger" );

    return (
        <Bar>
            <span className="title">
                Yoast query monitor
            </span>
            <button onClick={ closeDrawer }>
                <span className="dashicons dashicons-no-alt"></span>
            </button>
        </Bar>
    )
}

const QueryLogger = () => {
    const open = useSelect( select => {
        return select( "yoast/query-logger").isDrawerOpen();
    }, [] );

    return (
        <Fragment>
            { open ? <Drawer><Menu /></Drawer> : null }
        </Fragment>
    )
}

jQuery( () => {
    const box = document.createElement( "div" );
    box.className = "yoast-query-logger";
    
    document.body.appendChild( box );
    
    render(
        <AsyncModeProvider value={ true }>
            <QueryLogger />
        </AsyncModeProvider>
    , box );
} );
