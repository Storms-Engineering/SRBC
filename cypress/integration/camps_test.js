describe('Registration Test',function() {
    const baseURL = "http://127.0.0.1";
    //Login to wordpress
    beforeEach( function() {
        cy.visit( baseURL + '/wp-login.php' );
        cy.wait( 1000 );
        cy.get( '#user_login' ).type( Cypress.env( "wp_user" ) );
        cy.get( '#user_pass' ).type( Cypress.env( "wp_pass" ) );
        cy.get( '#wp-submit' ).click();
    } );
    it( 'Create new camp with UI', function() {
        cy.visit( baseURL + '/wp-admin/admin.php?page=camp_management' );
        cy.contains('Add New Camp').click();
        cy.get( '[name="name"]' ).type( "A Test Camp" );
        cy.get( '[name="start_date"]' ).type( "2001-01-01" );
        cy.get( '[name="end_date"]' ).type( "2001-01-06" );
        cy.get( '.description' ).type( "A test description" );
        cy.get( '[name="grade_range"]' ).type( "2nd to 3rd" );
        cy.get( '[name="cost"]' ).type( "298" );
        cy.get( '[name="horse_cost"]' ).type( "0" );
        cy.get( '[name="horse_opt_cost"]' ).type( "0" );
        cy.get( '[name="horse_list_size"]' ).type( "0" );
        cy.get( '[name="horse_waiting_list_size"]' ).type( "0" );
        cy.get( '[name="waiting_list_size"]' ).type( "2" );
        cy.get( '[name="boy_registration_size"]' ).type( "1" );
        cy.get( '[name="girl_registration_size"]' ).type( "1" );
        cy.get( '[name="overall_size"]' ).type( "2" );
        
        //Start listening for network requests
        cy.server()
        // Listen for POST and check status 
        cy.route('POST', 'update_camps.php').as('createCamp')
        cy.contains("Create New Camp").click();
        cy.wait('@createCamp').its('status').should('eq', 200)
        
    } );

    it('Check if I can open a camp and modify and save it', function() {
        cy.visit( baseURL + '/wp-admin/admin.php?page=camp_management' );
        cy.contains('A Test Camp').click();
        cy.contains('Lakeside A Test Camp');
        cy.get('.modal-content .description').type('some other stuff');
        cy.server()
        // Listen for POST and check status 
        cy.route('POST', 'update_camps.php').as('saveCamp')
        cy.contains("Save Info & Close").click({force:true});
        cy.wait('@saveCamp').its('status').should('eq', 200)
    });

    //Doesn't work yet
    /*it('Test Deleting a camp', function() {
        cy.visit( baseURL + '/wp-admin/admin.php?page=camp_management' );
        cy.server()
        // Listen for POST and check status 
        cy.route('POST', 'update_camps.php').as('saveCamp')
        cy.contains('Delete').click();
        cy.wait('@saveCamp').its('status').should('eq', 200)
    });*/

    
})