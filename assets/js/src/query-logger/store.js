import { createReduxStore, register } from "@wordpress/data";

const DEFAULT_STATE = {
    showDrawer: true,
}

const TOGGLE_DRAWER = "TOGGLE_DRAWER";
const CLOSE_DRAWER = "CLOSE_DRAWER";

const actions = {
    toggleDrawer() {
        return {
            type: TOGGLE_DRAWER,
        };
    },
    closeDrawer() {
        return {
            type: CLOSE_DRAWER,
        }
    }
}

const store = createReduxStore( "yoast/query-logger", {
    reducer( state = DEFAULT_STATE, action ) {
        switch( action.type ) {
            case TOGGLE_DRAWER:
                return {
                    ...state,
                    showDrawer: ! state.showDrawer,
                };
            case CLOSE_DRAWER:
                return {
                    ...state,
                    showDrawer: false,
                };
        }
        return state;
    },
    actions,
    selectors: {
        isDrawerOpen( state ) {
            return state.showDrawer;
        },
    }
} );

register( store );
