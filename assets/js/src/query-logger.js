import { render } from "@wordpress/element";

jQuery( () => {
    const box = document.createElement( "div" );
    box.className = "Test 123";
    
    document.getElementById( "wpwrap" ).appendChild( box );
    
    render( <div>Test</div>, box );
} );


