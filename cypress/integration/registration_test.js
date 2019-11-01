describe('Registration Test',function() {
    const baseURL = "http://127.0.0.1";


    it( 'Fill out registration', function() {
        cy.visit( baseURL + '/camps/lakeside' );
        cy.contains('Register').click();
        cy.get( '[name="camper_first_name"]' ).type( "Test" );
        cy.get( '[name="camper_last_name"]' ).type( "Camper" );
        cy.get( '[name="birthday"]' ).type( "2001-01-01" );
        cy.get('[value="male"]').click();
        cy.get( '[name="parent_first_name"]' ).type( "Parent" );
        cy.get( '[name="parent_last_name"]' ).type( "Name" );
        cy.get( '[name="email"]' ).type( "armystorms@gmail.com" );
        cy.get( '#retyped_email' ).type( "armystorms@gmail.com" );
        cy.get( '[name="phone"]' ).type( "19071234567" );
        cy.get( '[name="phone2"]' ).type( "19079876543" );
        cy.get( '[name="address"]' ).type( "123 Solid Rock Rd" );
        cy.get( '[name="city"]' ).type( "Soldotna" );
        cy.get( '[name="state"]' ).type( "AK" );
        cy.get( '[name="zipcode"]' ).type( "99669" );
        var selects = cy.get('.legal');
        for(var i=0;i<selects.length;i++)
        {
            console.log(i);
            selects[i].select('agree');
        }
    })

})
