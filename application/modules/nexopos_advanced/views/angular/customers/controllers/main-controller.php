var customersMain          =   function( 
    $scope, 
    $http,
    $location, 
    customersTextDomain, 
    customersResource,
    customersTable, 
    paginationFactory,
    sharedValidate, 
    sharedTable,
    sharedTableActions,
    sharedTableHeaderButtons,   
    sharedAlert, 
    sharedEntryActions, 
    sharedDocumentTitle 
    
    ) {

    sharedDocumentTitle.set( '<?php echo _s( 'Liste des clients', 'nexopos_advanced' );?>' );
    
    $scope.textDomain           =   customersTextDomain;
    $scope.validate             =   new sharedValidate();
    $scope.table                =   new sharedTable( '<?php echo _s( 'Liste des clients', 'nexopos_advanced' );?>' );
    $scope.table.columns        =   customersTable.columns;
    $scope.table.entryActions   =   new sharedEntryActions();
    $scope.table.actions        =   new sharedTableActions();
    $scope.table.headerButtons  =   new sharedTableHeaderButtons();
    $scope.table.resource       =   customersResource;

    /** Adjust Entry actions **/
    _.each( $scope.table.entryActions, function( value, key ) {
        if( value.namespace == 'edit' ) {
            $scope.table.entryActions[ key ].path      =    '/customers/edit/';
        }
    });

    /**
     *  Table Get
     *  @param object query object
     *  @return void
    **/

    $scope.table.get        =   function( params ){
        customersResource.get( params,function( data ) {
            $scope.table.entries        =   data.entries;
            $scope.table.pages          =   Math.ceil( data.num_rows / $scope.table.limit );
        });
    }

    /**
     *  Table Delete
     *  @param object query
     *  @return void
    **/

    $scope.table.delete     =   function( params ){
        customersResource.delete( params, function( data ) {
            $scope.table.get();
        },function(){
            sharedAlert.warning( '<?php echo _s(
                'Une erreur s\'est produite durant l\'operation',
                'nexopos_advanced'
            );?>' );
        });
    }

    // Get Results
    $scope.table.limit      =   10;
    $scope.table.getPage(0);
}

customersMain.$inject    =   [ 
    '$scope', 
    '$http', 
    '$location',
    'customersTextDomain',  
    'customersResource', 
    'customersTable',
    'paginationFactory',
    'sharedValidate', 
    'sharedTable',
    'sharedTableActions',
    'sharedTableHeaderButtons',  
    'sharedAlert', 
    'sharedEntryActions', 
    'sharedDocumentTitle' 
];

tendooApp.controller( 'customersMain', customersMain );
