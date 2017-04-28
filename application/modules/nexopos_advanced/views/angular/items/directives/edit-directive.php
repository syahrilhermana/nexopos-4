<?php if( true == false ):?><script><?php endif;?>
tendooApp.directive( 'itemEdit', function(){
    return {
        restrict        :   'E',
        templateUrl     :   'templates/items/edit',
        controller      :   [ 
            
            '$scope',
            '$http',
            '$location',
            'itemsTypes',
            'itemsTabs',
            'itemsAdvancedFields',
            'itemsFields',
            'itemsResource',
            'itemsVariationsResource',
            'providersResource',
            'categoriesResource',
            'deliveriesResource',
            'unitsResource',
            'taxesResource',
            'departmentsResource',
            '$routeParams',
            'sharedDocumentTitle',
            'sharedValidate',
            'sharedRawToOptions',
            'sharedFieldEditor',
            'sharedAlert',
            'sharedMoment',
            'sharedFilterItem',
            'sharedResourceLoader',
            'sharedFormManager',
            'sharedCurrency',
            'localStorageService',

            function( 
                
                $scope,
                $http,
                $location,

                // internal dependencies

                itemsTypes,
                itemsTabs,
                itemsAdvancedFields,
                itemsFields,
                itemsResource,
                itemsVariationsResource,
                providersResource,
                categoriesResource,
                deliveriesResource,
                unitsResource,
                taxesResource,
                departmentsResource,
                $routeParams,

                // Shared Dependencies

                sharedDocumentTitle,
                sharedValidate,
                sharedRawToOptions,
                sharedFieldEditor,
                sharedAlert,
                sharedMoment,
                sharedFilterItem,
                sharedResourceLoader,
                sharedFormManager,
                sharedCurrency,

                // External dependencies
                localStorageService
            ) {

            
            $scope                      =   _.extend( $scope, new sharedFormManager );
            $scope.resourceLoader       =   new sharedResourceLoader;
            $scope.category_desc        =   '<?php echo __( 'Assigner une catégorie permet de regrouper les produits similaires.', 'nexopos_advanced' );?>';
            $scope.validate             =   new sharedValidate();
            $scope.taxes                =   new Array;
            $scope.groupLengthLimit     =   10;
            $scope.itemsTypes           =   itemsTypes;
            $scope.fields               =   itemsFields;
            $scope.advancedFields       =   itemsAdvancedFields;

            /**
            *  Detect Item Namespace
            *  @param void
            *  @return void
            **/

            $scope.initItem      =   function(a, b){

                $scope.item                 =   new itemsTabs();
                $scope.item.name            =   '';
                $scope.item.variations      =   [{
                    models          :       {
                        name        :   $scope.item.name
                    },
                    tabs            :       $scope.item.getTabs()
                }];

                _.each( $scope.item.variations, function( variation, $tab_id ) {
                    _.each( variation.tabs, function( tab, $tab_key ) {
                        tab.models      =   {};
                    });
                });

                switch( $location.path() ) {
                    case "/items/add/clothes" :
                        $scope.item.namespace    =   'clothes';
                    break;
                    case "/items/add/coupon" :
                        $scope.item.namespace   =   'coupon';
                    break;
                }

                // Selected Type
                _.each( itemsTypes, function( type, key ) {
                    if( type.namespace == $scope.item.typeNamespace ) {
                        $scope.item.selectedType   =   type;
                    }
                });

                // When everything seems to be done, then we can check if the item exist on the local store
                if( localStorageService.isSupported ) {
                    // The item is reset if you access from type selection
                    // Maybe a prompt can ask whether the saved item should be deleted :\ ?
                    if( $location.path() == '/items/types' ) {
                        localStorageService.remove( 'item' );
                    } else {
                        if( typeof localStorageService.get( 'item' ) === 'object' ) {

                            let savedItem           =   localStorageService.get( 'item' );
                            
                            if( savedItem != null ) {

                                _.each( savedItem, ( field, field_name) => {
                                    if( field_name != 'variations' ) {
                                        $scope.item[ field_name ]   =   field;
                                    }
                                });

                                let tabs        =   new itemsTabs;

                                _.each( savedItem.variations, ( savedVariation, key ) => {
                                    $scope.item.variations[ key ]   =   {
                                        models          :   savedVariation.models,
                                        tabs            :   $scope.item.getTabs()
                                    };

                                    //Looping tabs
                                    _.each( $scope.item.variations[ key ].tabs, ( tab, tab_key ) => {
                                        tab.models      =   savedVariation.tabs[ tab_key ].models
                                    });
                                });
                            }
                        
                        }
                    }
                }
            }

            /**
            * Close Init
            * @param void
            * @return void
            **/
            
            $scope.closeInit                =   function () {
                // Display a dynamic price when a taxes is selected
                sharedFieldEditor( 'sale_price', itemsAdvancedFields.basic ).show          =   function( tab, item ) {
                    if( $scope.item.ref_taxe ) {
                        if( angular.isUndefined( $scope.taxes[ $scope.item.ref_taxe ] ) ) {
                            // To Avoid several calls to the database
                            $scope.taxes[ $scope.item.ref_taxe ]           =   {};
                            taxesResource.get({
                                id      :   $scope.item.ref_taxe
                            },function( entries ) {
                                $scope.taxes[ $scope.item.ref_taxe ]       =   entries;
                            });

                            if( angular.isDefined( tab.models.sale_price ) ) {
                                if( $scope.taxes[ $scope.item.ref_taxe ].type == 'percent' ) {
                                    var percentage      =   ( parseFloat( tab.models.sale_price ) * parseFloat( $scope.taxes[ $scope.item.ref_taxe ].value ) ) / 100;
                                    var newPrice        =   parseFloat( tab.models.sale_price ) + percentage;
                                    this.addon          =   sharedCurrency.toAmount( newPrice )
                                } else {
                                    var newPrice        =   parseFloat( tab.models.sale_price ) + parseFloat( $scope.taxes[ $scope.item.ref_taxe ].value );
                                    this.addon          =   sharedCurrency.toAmount( newPrice )
                                }
                            }
                        }

                        if( _.keys( $scope.taxes[ $scope.item.ref_taxe ] ).length > 0 ) {
                            if( angular.isDefined( tab.models ) ) {
                                if( angular.isDefined( tab.models.sale_price ) ) {
                                    if( $scope.taxes[ $scope.item.ref_taxe ].type == 'percent' ) {
                                        var percentage      =   ( parseFloat( tab.models.sale_price ) * parseFloat( $scope.taxes[ $scope.item.ref_taxe ].value ) ) / 100;
                                        var newPrice        =   parseFloat( tab.models.sale_price ) + percentage;
                                        this.addon          =   sharedCurrency.toAmount( newPrice )
                                    } else {
                                        var newPrice        =   parseFloat( tab.models.sale_price ) + parseFloat( $scope.taxes[ $scope.item.ref_taxe ].value );
                                        this.addon          =   sharedCurrency.toAmount( newPrice )
                                    }
                                }
                            }
                        }
                    }
                    return true;
                }

                // Init Item
                $scope.initItem();
            }

            /**
            * Load Item
            * @param void
            * @return void
            **/
            
            $scope.loadItem 	            =   function(){
                itemsResource.get({
                    id  :   $routeParams.id
                }, ( item ) => {
                    $scope.closeInit();

                    // Assign available field to the item
                    // When the item is completely loaded
                    $scope.fields.forEach( ( field ) => {
                        $scope.item[ field.model ]   =   item[ field.model ];
                    });

                    let emptyVariation              =   angular.copy( $scope.item.variations[0] );
                    $scope.item.variations          =   [];

                    item.variations.forEach( ( variation, index ) => {
                        
                        $scope.item.variations[ index ]     =   emptyVariation;

                        // Browse field model name and add it to the item variation
                        let tabId       =   0;

                        for( let tab in $scope.advancedFields ) {
                            
                            $scope.advancedFields[ tab ].forEach( ( field ) => {

                                if( field.type != 'group' ) {
                                    console.log( tabId );
                                    $scope.item.variations[ index ].tabs[ tabId ].models[ field.model ]   =   variation[ field.model ];
                                    // console.log( variation[ field.model ] );
                                } else {
                                    alert ('okkk' );
                                    // This works for groups
                                    /** 
                                    $scope.item.variations[ index ].tabs[ tabId ].models[ field.model ]     =   [];
                                    
                                    // Looping groups
                                    for( let groupIndex in variation[ field.model ] ) {
                                        
                                        // Looping the subField to get their model name
                                        if( typeof field.subFields != 'undefined' ) {

                                            // create group model
                                            $scope.item.variations[ index ].tabs[ tabId ].models[ field.model ][ groupIndex ]           =   {}
                                            $scope.item.variations[ index ].tabs[ tabId ].models[ field.model ][ groupIndex ].models    =   {};

                                            field.subFields.forEach( ( subField ) => {
                                                // [ subField.model ] );
                                                $scope.item.variations[ index ].tabs[ tabId ].models[ field.model ][ groupIndex ].models[ subField.model ]     =   variation[ field.model ][ groupIndex ][ subField.model ];   
                                            });
                                        }
                                    }
                                    **/
                                }                                
                            });   
                            tabId++;                       
                        };
                    });   
                });            
            }

            // Resources Loading
            $scope.resourceLoader.push({
                resource    :   providersResource,
                success    :   function( data ) {
                    sharedFieldEditor( 'ref_provider', itemsAdvancedFields.stock ).options        =   sharedRawToOptions( data.entries, 'id', 'name' );
                }   
            }).push({
                resource    :   categoriesResource,
                success    :   function( data ) {
                    sharedFieldEditor( 'ref_category', $scope.fields ).options   =   sharedRawToOptions( data.entries, 'id', 'name' );
                }
            }).push({
                resource    :   deliveriesResource,
                success    :   function( data ) {
                    sharedFieldEditor( 'ref_delivery', itemsAdvancedFields.stock ).options   =   sharedRawToOptions( data.entries, 'id', 'name' );
                }
            }).push({
                resource    :   unitsResource,
                success    :   function( data ) {
                    sharedFieldEditor( 'ref_unit', $scope.fields ).options        =   sharedRawToOptions( data.entries, 'id', 'name' );
                }
            }).push({
                resource    :   taxesResource,
                success    :   function( data ) {
                    sharedFieldEditor( 'ref_taxe', $scope.fields ).options        =   sharedRawToOptions( data.entries, 'id', 'name' );
                }
            }).push({
                resource    :   departmentsResource,
                success    :   function( data ) {
                    sharedFieldEditor( 'ref_department', $scope.fields ).options        =   sharedRawToOptions( data.entries, 'id', 'name' );
                    $scope.loadItem();
                }
            });

            $scope.resourceLoader.run();
        }]
    }
})