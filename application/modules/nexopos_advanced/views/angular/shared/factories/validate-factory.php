<?php if( true == false ):?>
<script type="text/javascript">
<?php endif;?>
angular.element( document ).ready( () => {
    tendooApp.factory( 'sharedValidate', function(){
        return function(){
            var expression  =   {
                required: /^\s*$/,
                url: /((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/,
                email: /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/,
                number: /^\d+$/,
                alpha_char  :    /^[a-zA-Z]+$/,
                alpha_num   :   /^[a-zA-Z0-9]+$/,
                ip          :   /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?).){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/,
                digit       :   /^[0-9]+$/,
                decimal       :   /^[+-]?\d+(\.\d+)?$/
            };

            var $this       =   this;

            /**
            *  Individual Validation
            *  @param object field
            *  @param object item
            *  @return object error
            **/

            this.__run          =   function( field, item ) {
                var errors      =   {};

                if( angular.isDefined( field.validation ) ) {

                    _.each( field.validation, function( value, rule ) {

                        // If a field has a specific formating, let's apply this before validating
                        if( typeof field.beforeValidation != 'undefined' ) {
                            item[ field.model ]     =   field.beforeValidation( item[ field.model ] );
                        }

                        if( rule == 'required' && value == true ) {
                            if( ! angular.isDefined( item[ field.model ] ) || item[ field.model ] == null ) {
                                errors[ field.model ]           =   {};
                                errors[ field.model ].msg       =   '<?php echo _s( 'Ce champ est requis.', 'nexopos_advanced' );?>';
                                errors[ field.model ].label     =   field.label;
                            } else if( item[ field.model ].match( expression.required ) ) {
                                errors[ field.model ]           =   {};
                                errors[ field.model ].msg       =   '<?php echo _s( 'Ce champ est requis.', 'nexopos_advanced' );?>';
                                errors[ field.model ].label     =   field.label;
                            }
                        }

                        if(
                            rule == 'email' &&
                            value == true &&
                            typeof item[ field.model ] != 'undefined' &&
                            angular.equals({}, errors )
                        ) {
                            if( item[ field.model ] != null ) {
                                if( ! item[ field.model ].match( expression.email ) ) {
                                    errors[ field.model ]           =   {};
                                    errors[ field.model ].msg       =   '<?php echo _s( 'La valeur %% n\'est pas une adresse email valide.', 'nexopos_advanced' );?>';
                                    errors[ field.model ].label     =   field.label;
                                }
                            }
                        }

                        if(
                            rule == 'min_value' &&
                            typeof item[ field.model ] != 'undefined' &&
                            angular.equals({}, errors )
                        ) {
                            if( item[ field.model ] != null ) {
                                if( item[ field.model ].length < value ) {
                                    errors[ field.model ]           =   {};
                                    errors[ field.model ].msg       =   '<?php echo _s( 'La longueur de ce champ ne doit pas être inférieure à {0}.', 'nexopos_advanced' );?>' . format( value );
                                    errors[ field.model ].label     =   field.label;
                                }
                            }
                        }

                        if(
                            rule == 'max_value' &&
                            typeof item[ field.model ] != 'undefined' &&
                            angular.equals({}, errors )
                        ) {
                            if( item[ field.model ] != null ) {
                                if( item[ field.model ].length > value ) {
                                    errors[ field.model ]           =   {};
                                    errors[ field.model ].msg       =   '<?php echo _s( 'La longueur de ce champ ne doit pas excéder {0}', 'nexopos_advanced' );?>' . format( value );
                                    errors[ field.model ].label     =   field.label;
                                }
                            }
                        }

                        if(
                            rule == 'numeric' &&
                            value == true &&
                            typeof item[ field.model ] != 'undefined' &&
                            angular.equals({}, errors )
                        ) {
                            if( item[ field.model ] != null ) {
                                if( ! item[ field.model ].match( expression.number ) ) {
                                    errors[ field.model ]           =   {};
                                    errors[ field.model ].msg       =   '<?php echo _s( 'Ce champ devrait avoir une valeur numérique.', 'nexopos_advanced' );?>';
                                    errors[ field.model ].label     =   field.label;
                                }
                            }
                        }

                        if(
                            rule == 'decimal' &&
                            value == true &&
                            typeof item[ field.model ] != 'undefined' &&
                            angular.equals({}, errors )
                        ) {
                            if( item[ field.model ] != null ) {
                                if( ! item[ field.model ].match( expression.decimal ) ) {
                                    errors[ field.model ]           =   {};
                                    errors[ field.model ].msg       =   '<?php echo _s( 'Ce champ devrait avoir une valeur numérique/décimale.', 'nexopos_advanced' );?>';
                                    errors[ field.model ].label     =   field.label;
                                }
                            }
                        }

                        /**
                        * Callback Rules
                        * Define a callback on a field, which can make an http request.
                        **/

                        if( rule == "callback" ) {
                            errors[ field.model ].callback  =   value;
                        }

                    });
                }

                item[ field.model ]     =   angular.isUndefined( item[ field.model ]  ) ? '' : item[ field.model ];

                return errors;
            }

            this.run        =   function( fields, item ) {

                return;
                var errors          =   {};

                _.each( fields, function( field ){
                    // extends current field errors
                    let singleRunResult     =   $this.__run( field, item ) ;

                    errors          =   _.extend( errors, singleRunResult );
                });

                // replace template on error if exists
                errors              =   this.__replaceTemplate( errors );

                return this.__response( errors );
            };

            /**
            *  Turn into response
            *  @param object error
            *  @return object
            **/

            this.__response     =   function( errors ) {
                return {
                    isValid     :   angular.equals({}, errors ) ? true : false,
                    errors      :   errors
                }
            }

            this.focus      =   function( field, item, $event ) {
                var fieldClass      =   '.' + field.model + '-helper';
                if( angular.isDefined( $event ) ) {
                    angular.element( $event.target ).closest( '.form-group' ).removeClass( 'has-error' );
                    angular.element( $event.target ).closest( '.form-group' ).find( 'p.help-block' ).text( field.desc );
                } else {
                    angular.element( fieldClass ).closest( '.form-group' ).removeClass( 'has-error' );
                    angular.element( fieldClass ).text( field.desc );
                }
            }

            /**
            *  unique validation
            *  @param object fields
            *  @param object item
            *  @return void
            **/

            this.blur       =   function( field, item, $event ) {
                var validation      =   this.__run( field, item );
                var response        =   this.__response( validation );
                var errors          =   this.__replaceTemplate( response.errors );
                var fieldClass      =   '.' + field.model + '-helper';

                if( ! response.isValid ) {
                    if( angular.isDefined( $event ) ) {
                        angular.element( $event.target ).closest( '.form-group' ).removeClass( 'has-success' );
                        angular.element( $event.target ).closest( '.form-group' ).find( 'p.help-block' ).text( errors[ field.model ].msg );
                        angular.element( $event.target ).closest( '.form-group' ).addClass( 'has-error' );
                    } else {
                        angular.element( fieldClass ).closest( '.form-group' ).removeClass( 'has-success' );
                        angular.element( fieldClass ).text( errors[ field.model ].msg );
                        angular.element( fieldClass ).closest( '.form-group' ).addClass( 'has-error' );
                    }
                }
            }

            /**
            *  Blur all fields to display errors
            *  @param object fields
            *  @return void
            **/

            this.blurAll            =   function( fields, item ) {
                _.each( fields, function( field ) {
                    $this.blur( field, item );
                });
            }

            /**
            *  Replace template
            *  @param  object validation object
            *  @return object
            **/

            this.__replaceTemplate    =   function( errors ) {
                _.each( errors, function( value, key ) {
                    errors[ key ].msg   =   value.msg.replace( '%%', value.label );
                });
                return errors;
            }

            this.walker                 =   function( fields, item, index = 0, mainResolve, errors = {} ) {
                return new Promise( ( resolve, reject ) => {

                    let length              =   fields.length;
                    let field               =   fields[ index ];

                    // to avoid mainResolve overwrithing
                    if( index == 0 ) {
                        mainResolve     =   resolve;
                    }

                    // probably when the walker reach the end
                    if( typeof field == 'undefined' ) {
                        // when the walker has done
                        errors              =   this.__replaceTemplate( errors );
                        let response        =   this.__response( errors ); 

                        _.each( fields, ( field ) => {
                            if( typeof response.errors[ field.model ] != 'undefined' ) {
                                let fieldClass      =   '.' + field.model + '-helper';
                                if( ! response.isValid ) {
                                    
                                    angular.element( fieldClass ).closest( '.form-group' ).removeClass( 'has-success' );
                                    angular.element( fieldClass ).text( errors[ field.model ].msg );
                                    angular.element( fieldClass ).closest( '.form-group' ).addClass( 'has-error' );
                                }
                            }                            
                        });

                        return mainResolve( errors );
                    }

                    let promise             =   new Promise( ( _resolve, _reject ) => {
                        let run             =   this.__run( field, item );
                        errors              =   _.extend( errors, run );
                        if( _.keys( run ).length > 0 ) {
                            // before rejecting, let make sure it's not a callback
                            if( typeof run.callback != 'undefined' ) {
                                // Test Callback Promise
                                let callbackPromise     =   run.callback( field, item );
                                callbackPromise.then( ( errors )=>{
                                    _resolve({
                                        fields, 
                                        item, 
                                        index   :   index+1,
                                        errors
                                    });
                                }, ( errors ) => {
                                    _reject({
                                        fields, 
                                        item, 
                                        index   :   index+1,
                                        errors
                                    });
                                });
                            } else {
                                // index + 1 to move to the next fields
                                _reject({
                                    fields, 
                                    item, 
                                    index   :   index+1,
                                    errors
                                }); 
                            }                        
                        } else {
                            _resolve({
                                fields  :   fields, 
                                item    :   item, 
                                index   :   index+1,
                                errors
                            });
                        }
                    });

                    // Run Template Remplacement
                    promise.then( ({ fields, item, index, errors }) => {
                        // if there is no error, just validate next fields
                        this.walker( fields, item, index, mainResolve, errors );
                    }, ({ fields, item, index, errors }) => {

                        this.walker( fields, item, index, mainResolve, errors );
                    });
                });
            }

            this.variations_walker       =   function( variation_fields, variations, index = 0, mainResolve ) {

                return new Promise( ( resolve, reject ) => {

                    let variation       =   variations[ index ];

                    if( index == 0 ) {
                        mainResolve     =   resolve;
                    }

                    if( typeof variation == 'undefined' ) {
                        return mainResolve();
                    }

                    let promise         =   new Promise( ( _resolve, _reject ) => {

                        this.tabs_walker( variation_fields, variations[ index ].tabs ).then( () => {
                            // When all variation tab has been walked over
                            _resolve({
                                variation_fields,
                                variations,
                                index   :   index+1,
                                mainResolve
                            });
                        }); 

                    });

                    promise.then( ({ variation_fields, variations, index, mainResolve }) => {
                        this.variations_walker( variation_fields, variations, index, mainResolve );
                    })
                })
            }

            this.tabs_walker             =   function( fields, tabs, index = 0, mainResolve ) {

                return new Promise( ( resolve, reject ) => {
                    if( index == 0 ) {
                        mainResolve     =   resolve;
                    }

                    if( typeof tabs[ index ] == 'undefined' ) {
                        return mainResolve();
                    }

                    let promise         =   new Promise( ( _resolve, _reject ) => { 
                        _.each( fields[ tabs[ index ].namespace ], ( field ) => {
                            if( typeof tabs[ index ].models[ field.model ] == 'undefined' ) {
                                tabs[ index ].models[ field.model ]     =   '';    
                            }
                        });

                        console.log( fields, tabs[ index ].namespace );

                        this.walker( fields[ tabs[ index ].namespace ], tabs[ index ].models ).then( function( errors ){
                            _resolve({ fields, tabs, index : index + 1, mainResolve });
                        });                
                    })

                    promise.then( ({ tabs, item, index, mainResolve }) => {
                        this.tabs_walker( fields, tabs, index, mainResolve );
                    });
                })

            }
        }
    });

});