jQuery( document ).ready( function ( $ ) {
    /**
	 * Function to activate datatables
	 */
    $.ss_testimonial_datatable = $( '#ss-testimonial-datatable' ).dataTable( {
            'processing': true,
            'serverSide': false,
            "ordering": false,
            "pageLength": 1,
            'ajax': {
                url : ss_api_post_submit_action.root + 'ss-wp-9/v1/testimonials?is_datatable=true',
                type: 'GET',
                dataSrc: "data"
            },
            'columns': [
                { 'data' : 'tst_author' },
                { 'data' : 'post_content' },
                { 'data' : 'tst_date' },
                { 'data' : 'tst_rate' }
            ]

    } );


    /**
	 * Function to handle select, insert, update, and delete
	 *
	 * @param string    ss_action Action type : insert, update, or delete.
	 * @param int       ss_post_id Post ID, could be empty for action insert.
	 */
    function ss_api_crud_handlers( ss_action, ss_post_id = 0 ) {
        var ss_ajax_method      = "POST";
        var ss_ajax_action_url  = "ss-wp-9/v1/testimonials";
        var ss_post_data        = {};

        if( ss_action == 'insert' || ss_action == 'update' ) {
            //-- insert / update new post
            ss_ajax_method      = "POST";

            //-- get form input data
            ss_tst_author   = $( '#ss-input-tst-author' ).val();
            ss_tst_content  = $( '#ss-input-tst-content' ).val();
            ss_tst_date     = $( '#ss-input-tst-date' ).val();
            ss_tst_rate     = $( '#ss-input-tst-rate' ).val();

            //-- set action url & post data
            if( ss_action == 'insert' ) {
                ss_ajax_action_url  = "ss-wp-9/v1/testimonials";

                //-- set post data
                ss_post_data = {
                    author: ss_tst_author,
                    content: ss_tst_content,
                    date: ss_tst_date,
                    rate: ss_tst_rate,
                    status: 'publish'
                };
            } else if( ss_action == 'update' ) {
                ss_ajax_method      = "PATCH";
                ss_ajax_action_url  = 'ss-wp-9/v1/testimonials/' + ss_post_id;

                ss_tst_author   = $( '.ss-api-form-update-post #ss-input-tst-author' ).val();
                ss_tst_content  = $( '.ss-api-form-update-post #ss-input-tst-content' ).val();
                ss_tst_date     = $( '.ss-api-form-update-post #ss-input-tst-date' ).val();
                ss_tst_rate     = $( '.ss-api-form-update-post #ss-input-tst-rate' ).val();

                //-- set post data
                ss_post_data = {
                    id: ss_post_id,
                    author: ss_tst_author,
                    content: ss_tst_content,
                    date: ss_tst_date,
                    rate: ss_tst_rate
                };
            }
            
            //-- end insert / update new post
        } else if( ss_action == 'delete' ) {
            //-- delete post by id
            ss_ajax_method      = "DELETE";
            ss_ajax_action_url  = 'ss-wp-9/v1/testimonials/' + ss_post_id;

            //-- set post data
            ss_post_data = {
                id: ss_post_id
            };
        } else if( ss_action == 'select_spesific' ) {
            //-- get spesific post by ID
            ss_ajax_method      = "GET";
            ss_ajax_action_url  = 'ss-wp-9/v1/testimonials/' + ss_post_id;

            //-- set post data
            ss_post_data = {
                id: ss_post_id
            };

        }

        //-- execute ajax
        $.ajax( {
            method: ss_ajax_method,
            url: ss_api_post_submit_action.root + ss_ajax_action_url,
            data: ss_post_data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', ss_api_post_submit_action.nonce );
            },
            success : function( response ) {
                //-- get current max page
                var ss_max_page = parseInt( $( '.pagination-buttons-container' ).attr( 'data-max-page' ) );

                //-- show success notification
                if( ss_action != 'select_spesific' ) {
                    alert( ss_api_post_submit_action.success );
                }

                if( ss_action == 'delete' ) {
                    //-- remove element when data successfully deleted
                    $(  '.post-' + ss_post_data.id ).remove();

                    //-- update max page at pagination container
                    $( '.pagination-buttons-container' ).attr( 'data-max-page', (ss_max_page-1) );
                    $( '.page-number .max-page' ).html( (ss_max_page-1) );
                } else if( ss_action == 'select_spesific' ) {
                    //-- select spesific post data by ID and apply it to the update form
                    var ss_tst_author   = response.data[ 0 ].tst_author;
                    var ss_tst_content  = response.data[ 0 ].tst_content;
                    var ss_tst_date     = response.data[ 0 ].tst_date;
                    var ss_tst_rate     = response.data[ 0 ].tst_rate;

                    $( '.ss-api-form-update-post' ).attr( 'data-post-id', ss_post_data.id );

                    //-- set form value
                    $( '.ss-api-form-update-post #ss-input-tst-author' ).val( ss_tst_author );
                    $( '.ss-api-form-update-post #ss-input-tst-content' ).val( ss_tst_content );
                    $( '.ss-api-form-update-post #ss-input-tst-date' ).val( ss_tst_date );
                    $( '.ss-api-form-update-post #ss-input-tst-rate' ).val( ss_tst_rate );
                } else if( ss_action == 'update' ) {
                    //-- if successfully updating the data

                    //-- hide update form
                    $( '.ss-api-form-update-post' ).hide();
                } else if( ss_action == 'insert' ) {
                    //-- update max page at pagination container
                    $( '.pagination-buttons-container' ).attr( 'data-max-page', (ss_max_page+1) );
                    $( '.page-number .max-page' ).html( (ss_max_page+1) );
                }

                //-- update jquery datatables
                $.ss_testimonial_datatable.api().ajax.reload();

                //-- update testimonial results ( non datatables )
                if ( $.isFunction( $.ss_reload_testimonial ) ) {
                    $.ss_reload_testimonial();
                }
            },
            fail : function( response ) {
                alert( ss_api_post_submit_action.failure );
            }
        } );
    }

    //-- insert post on submit handlers
    $( '.ss-api-form-insert-post' ).on( 'submit', function( e ) {
        e.preventDefault();
 
        ss_api_crud_handlers( 'insert' );
    });

    //-- delete post button clicked
    $( '.ajax-post-results-container' ).on( 'click', '.api-delete-post', function( e ) {
        e.preventDefault();

        //-- get post id from data attribute
        var ss_post_id = $( this ).data( 'post-id' );

        ss_api_crud_handlers( 'delete', ss_post_id );
    } );

    //-- update post button clicked ( in each post )
    $( '.ajax-post-results-container' ).on( 'click', '.api-update-post', function( e ) {
        e.preventDefault();

        //-- get post id from data attribute
        var ss_post_id = $( this ).data( 'post-id' );

        //-- show update post
        $( '.ss-api-form-update-post' ).show();

        //-- scroll down the viewport
        $( 'body, html' ).scrollTop( $( '.ss-api-form-update-post' ).offset().top - 150 );

        //-- get spesific post by id then set form value
        ss_api_crud_handlers( 'select_spesific', ss_post_id );
    } );

    //-- update post submit button clicked ( in update forms )
    $( '.ss-api-form-update-post' ).on( 'submit', function( e ) {
        e.preventDefault();

        ss_api_crud_handlers( 'update', $( this ).data( 'post-id' ) );
    } );
} );