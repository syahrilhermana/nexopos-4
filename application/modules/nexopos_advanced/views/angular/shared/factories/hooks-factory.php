angular.element( 'document' ).ready( () => {
    tendooApp.factory( 'sharedHooks', function(){
        return function() {
            var slice = Array.prototype.slice;
		
            /**
            * Contains the hooks that get registered with this EventManager. The array for storage utilizes a "flat"
            * object literal such that looking up the hook utilizes the native object literal hash.
            */
            var STORAGE = {
                actions : {},
                filters : {}
            };

            /**
            * Adds an action to the event manager.
            *
            * @param action Must contain namespace.identifier
            * @param callback Must be a valid callback function before this action is added
            * @param [priority=10] Used to control when the function is executed in relation to other callbacks bound to the same hook
            * @param [context] Supply a value to be used for this
            */
            this.addAction      =   function ( action, callback, priority, context ) {
                if( typeof action === 'string' && typeof callback === 'function' ) {
                    priority = parseInt( ( priority || 10 ), 10 );
                    this._addHook( 'actions', action, callback, priority, context );
                }

                return this;
            }

            /**
            * Performs an action if it exists. You can pass as many arguments as you want to this function; the only rule is
            * that the first argument must always be the action.
            */
            this.doAction   =   function ( /* action, arg1, arg2, ... */ ) {
                var args = slice.call( arguments );
                var action = args.shift();

                if( typeof action === 'string' ) {
                    this._runHook( 'actions', action, args );
                }

                return this;
            }

            /**
            * Removes the specified action if it contains a namespace.identifier & exists.
            *
            * @param action The action to remove
            * @param [callback] Callback function to remove
            */
            this.removeAction       =   function ( action, callback ) {
                if( typeof action === 'string' ) {
                    this._removeHook( 'actions', action, callback );
                }

                return this;
            }

            /**
            * Adds a filter to the event manager.
            *
            * @param filter Must contain namespace.identifier
            * @param callback Must be a valid callback function before this action is added
            * @param [priority=10] Used to control when the function is executed in relation to other callbacks bound to the same hook
            * @param [context] Supply a value to be used for this
            */
            this.addFilter  =   function ( filter, callback, priority, context ) {
                if( typeof filter === 'string' && typeof callback === 'function' ) {
                    priority = parseInt( ( priority || 10 ), 10 );
                    this._addHook( 'filters', filter, callback, priority, context );
                }

                return this;
            }

            /**
            * Performs a filter if it exists. You should only ever pass 1 argument to be filtered. The only rule is that
            * the first argument must always be the filter.
            */
            this.applyFilters       =   function ( /* filter, filtered arg, arg2, ... */ ) {
                var args = slice.call( arguments );
                var filter = args.shift();

                if( typeof filter === 'string' ) {
                    return this._runHook( 'filters', filter, args );
                }

                return this;
            }

            /**
            * Removes the specified filter if it contains a namespace.identifier & exists.
            *
            * @param filter The action to remove
            * @param [callback] Callback function to remove
            */
            this.removeFilter   =   function ( filter, callback ) {
                if( typeof filter === 'string') {
                    this._removeHook( 'filters', filter, callback );
                }

                return this;
            }

            /**
            * Removes the specified hook by resetting the value of it.
            *
            * @param type Type of hook, either 'actions' or 'filters'
            * @param hook The hook (namespace.identifier) to remove
            * @private
            */
            this._removeHook    =   function ( type, hook, callback, context ) {
                var handlers, handler, i;
                
                if ( !STORAGE[ type ][ hook ] ) {
                    return;
                }
                if ( !callback ) {
                    STORAGE[ type ][ hook ] = [];
                } else {
                    handlers = STORAGE[ type ][ hook ];
                    if ( !context ) {
                        for ( i = handlers.length; i--; ) {
                            if ( handlers[i].callback === callback ) {
                                handlers.splice( i, 1 );
                            }
                        }
                    }
                    else {
                        for ( i = handlers.length; i--; ) {
                            handler = handlers[i];
                            if ( handler.callback === callback && handler.context === context) {
                                handlers.splice( i, 1 );
                            }
                        }
                    }
                }
            }

            /**
            * Adds the hook to the appropriate storage container
            *
            * @param type 'actions' or 'filters'
            * @param hook The hook (namespace.identifier) to add to our event manager
            * @param callback The function that will be called when the hook is executed.
            * @param priority The priority of this hook. Must be an integer.
            * @param [context] A value to be used for this
            * @private
            */
            this._addHook   =   function ( type, hook, callback, priority, context ) {
                var hookObject = {
                    callback : callback,
                    priority : priority,
                    context : context
                };

                // Utilize 'prop itself' : http://jsperf.com/hasownproperty-vs-in-vs-undefined/19
                var hooks = STORAGE[ type ][ hook ];
                if( hooks ) {
                    hooks.push( hookObject );
                    hooks = _hookInsertSort( hooks );
                }
                else {
                    hooks = [ hookObject ];
                }

                STORAGE[ type ][ hook ] = hooks;
            }

            /**
            * Use an insert sort for keeping our hooks organized based on priority. This function is ridiculously faster
            * than bubble sort, etc: http://jsperf.com/javascript-sort
            *
            * @param hooks The custom array containing all of the appropriate hooks to perform an insert sort on.
            * @private
            */
            this._hookInsertSort    =   function ( hooks ) {
                var tmpHook, j, prevHook;
                for( var i = 1, len = hooks.length; i < len; i++ ) {
                    tmpHook = hooks[ i ];
                    j = i;
                    while( ( prevHook = hooks[ j - 1 ] ) &&  prevHook.priority > tmpHook.priority ) {
                        hooks[ j ] = hooks[ j - 1 ];
                        --j;
                    }
                    hooks[ j ] = tmpHook;
                }

                return hooks;
            }

            /**
            * Runs the specified hook. If it is an action, the value is not modified but if it is a filter, it is.
            *
            * @param type 'actions' or 'filters'
            * @param hook The hook ( namespace.identifier ) to be ran.
            * @param args Arguments to pass to the action/filter. If it's a filter, args is actually a single parameter.
            * @private
            */
            this._runHook       =   function ( type, hook, args ) {
                var handlers = STORAGE[ type ][ hook ], i, len;
                
                if ( !handlers ) {
                    return (type === 'filters') ? args[0] : false;
                }

                len = handlers.length;
                if ( type === 'filters' ) {
                    for ( i = 0; i < len; i++ ) {
                        args[ 0 ] = handlers[ i ].callback.apply( handlers[ i ].context, args );
                    }
                } else {
                    for ( i = 0; i < len; i++ ) {
                        handlers[ i ].callback.apply( handlers[ i ].context, args );
                    }
                }

                return ( type === 'filters' ) ? args[ 0 ] : true;
            }

            // return all of the publicly available methods
            return this;
        }
    })
})