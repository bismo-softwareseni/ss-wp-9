jQuery( document ).ready( function ( $ ) {
    //-- button ajax pagination click handlers
    $( '.button-ajax-pagination' ).on( 'click', function() {
        //-- get pagination variables
        var ss_current_page = parseInt( $( this ).parent().attr( 'data-current-page' ) );
        var ss_max_page     = parseInt( $( this ).parent().attr( 'data-max-page' ) );
        var ss_post_perpage = parseInt( $( this ).parent().attr( 'data-post-perpage' ) );
        var ss_goto_page    = parseInt( $( this ).attr( 'data-page' ) );

        //-- set ajax variable
        var ss_ajax_method      = "GET";
        var ss_ajax_action_url  = "ss-wp-9/v1/testimonials";
        var ss_post_data        = {
            per_page : ss_post_perpage,
            page : ss_goto_page
        };

        //-- request new page
        $.ajax( {
            method: ss_ajax_method,
            url: ss_api_post_pagination.root + ss_ajax_action_url,
            data: ss_post_data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', ss_api_post_pagination.nonce );
            },
            success : function( response ) {
                //-- clear current container
                $( '.ajax-post-results-container' ).html( '' );

                for( var i=0; i<response.length; i++ ) {
                    var ss_result_tags  = '<h5 class="post-'+ response[ i ].ID +'">';
                    ss_result_tags     += '<a href="'+ response[ i ].guid +'">'+ response[ i ].post_content +'</a>';
                    ss_result_tags     += '</h5>';

                    $( '.ajax-post-results-container' ).append( ss_result_tags );
                }

                //-- update pagination button's variable
                var ss_next_page = 0;
                var ss_prev_page = 0;

                //-- prev page
                if( (ss_goto_page-1) >= 1 ) {
                    ss_prev_page = (ss_goto_page-1);
                } else {
                    ss_prev_page = 1;
                }

                //-- next page
                if( (ss_goto_page+1) <= ss_max_page ) {
                    ss_next_page = (ss_goto_page+1);
                } else {
                    ss_next_page    = ss_goto_page;
                }

                $( '.button-ajax-pagination.prev-page' ).parent().attr( 'data-current-page', ss_goto_page );
                $( '.button-ajax-pagination.prev-page' ).attr( 'data-page', ss_prev_page );
                $( '.button-ajax-pagination.next-page' ).attr( 'data-page', ss_next_page );

                //-- update page number
                $( '.ajax-pagination-container .current-page' ).html( ss_goto_page );
            },
            fail : function( response ) {
                alert( ss_api_post_submit_action.failure );
            }
        } );
    } );
} );