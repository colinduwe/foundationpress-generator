( function( $ ) {

	// Basic client-side form validation.
	var form = $( '#generator-form' );
	var nameInput = $( '#foundationpress-generator-name' );
	var uriInput = $( '#foundationpress-generator-author-uri' );
	var slugInput = $( '#foundationpress-generator-slug' );
	// Set form inputs we check via JavaScript as not having errors because we have not checked for errors yet.
	nameInput.attr( 'aria-invalid', 'false' );
	uriInput.attr( 'aria-invalid', 'false' );
	slugInput.attr( 'aria-invalid', 'false' );

	// Listen for submit
	form.submit( function( e ) {
		
		// Get our values so we can check them.
		var name = $( '#foundationpress-generator-name' ).val();
		var uri = $( '#foundationpress-generator-author-uri' ).val();
		var slug = $( '#foundationpress-generator-slug' ).val();
		var errors = '';
		
		console.log(uri.length);
		console.log(slug.length);
		

		// Supply our error messages.
		// If theme name is empty.
		if ( ! name || 0 === name.length ) {
			errors += '<li><a href="#foundationpress-generator-name">Please specify a theme name</a>.</li>\n';
			nameInput.attr( 'aria-invalid', 'true' ).addClass('is-invalid-input');
		} else {
			// Reset aria-invalid attribue from any previous attempts.
			nameInput.attr( 'aria-invalid', 'false' ).removeClass('is-invalid-input');
		}
		// If the theme name is not empty, make sure it has no special characters.
		if ( name || 0 < name.length ) {
			if ( /[\'^£$%&*()}{@#~?><>,|=+¬"]/.test( name.trim() ) === true ) {
				errors += '<li>Theme name could not be used to generate valid theme name. Special characters are not allowed. <a href="#foundationpress-generator-name">Please go back and try again</a>.</li>\n';
				nameInput.attr( 'aria-invalid', 'true' ).addClass('is-invalid-input');
			} else {
				// Reset aria-invalid attribue from any previous attempts.
				nameInput.attr( 'aria-invalid', 'false' ).removeClass('is-invalid-input');
			}
		}
		// If the theme slug is not empty, make sure it has no special characters.
		if ( slug || 0 < slug.length ) {
			if ( /^[a-z0-9-]+$/i.test( slug.trim() ) === false ) {
				errors += '<li>Theme slug could not be used to generate valid function names. Special characters are not allowed. <a href="#foundationpress-generator-slug">Please go back and try again</a>.</li>\n';
				slugInput.attr( 'aria-invalid', 'true' ).addClass('is-invalid-input');
			} else {
				// Reset aria-invalid attribue from any previous attempts.
				slugInput.attr( 'aria-invalid', 'false' ).removeClass('is-invalid-input');
			}
		}
		// If the author uri is not empty, make sure it is a valid uri.
		if ( uri || 0 < uri.length ) {
			if ( /^(https?:\/\/)([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/.test( uri.trim() ) === false ) {
				errors += '<li>Author URI is not valid. Be sure to include <code>http://</code> in the URI. <a href="#foundationpress-generator-author-uri">Please go back and try again</a>.</li>\n';
				uriInput.attr( 'aria-invalid', 'true' ).addClass('is-invalid-input');
			} else {
				// Reset aria-invalid attribue from any previous attempts.
				uriInput.attr( 'aria-invalid', 'false' ).removeClass('is-invalid-input');
			}
		}

		// If we have errors from a previous try, let's remove them.
		if ( errors !== '' &&  $( '#error > ul li' ).length !== 0 ) {
			$( '#error > ul li' ).remove();
		}
		// If we have errors, let's show them.
		if ( errors !== '' ) {
			// We only create the error div and ul if we don't already have them.
			if ( ! $( '#error' ).length ) {
				var errorDiv = $( '<div>', { id: 'error', class: 'error alert callout', tabindex: '-1', role: 'alert' } );
				$( '#generator-form' ).prepend( errorDiv );
				$( '#error' ).append( '<ul>' );
			}
			// Let's place our errors and shift focus there.
			$( '#error > ul' ).append( errors );
			$( '#error' ).focus();
			e.preventDefault();
		}
		// If we have no errors, let's reset them.
		else if ( errors === '' ) {
			$( '#error > ul li' ).remove();
			nameInput.attr( 'aria-invalid', 'false' );
			uriInput.attr( 'aria-invalid', 'false' );
			slugInput.attr( 'aria-invalid', 'false' );
		}
	} );
} )( jQuery );

