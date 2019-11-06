describe('Registration Test',function() {
    const baseURL = "http://127.0.0.1";

    it ('Navigates to registration', function()
    {
        cy.visit( baseURL + '/camps/lakeside' );
        cy.contains('Register').click();
    });

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
        cy.get('.legal').eq('0').select("agree");
        cy.get('.legal').eq('1').select("agree");
        cy.get('.legal').eq('2').select("agree");
        

    });

    it( 'Fill out health Form', function() {
    cy.get( '[name="emergency_contact"]' ).type( "Someone Important" );
        cy.get( '[name="emergency_phone_home"]' ).type( "907-123-4567" );
        cy.get( '[name="emergency_phone_cell"]' ).type( "907-987-6543" );
        cy.get( '[name="recent_injury_illness"]' ).select("Yes");
        cy.get( '[name="ear_infections"]' ).select("Yes");
        cy.get( '[name="skin_problems"]' ).select("Yes");
        cy.get( '[name="sleepwalking"]' ).select("Yes");
        cy.get( '[name="chronic_recurring_illness"]' ).select("Yes");
        cy.get( '[name="glassses_contacts"]' ).select("Yes");
        cy.get( '[name="orthodontic_appliance"]' ).select("Yes");
        cy.get( '[name="mono"]' ).select("Yes");
        cy.get( '[name="current_medications"]' ).select("Yes");
        cy.get( '[name="frequent_headaches"]' ).select("Yes");
        cy.get( '[name="stomach_aches"]' ).select("Yes");
        cy.get( '[name="head_injury"]' ).select("Yes");
        cy.get( '[name="high_blood_pressure"]' ).select("Yes");
        cy.get( '[name="asthma"]' ).select("Yes");
        cy.get( '[name="seizures"]' ).select("Yes");
        cy.get( '[name="diabetes"]' ).select("Yes");
        cy.get( '[name="bed_wetting"]' ).select("Yes");
        cy.get( '[name="immunizations"]' ).select("Yes");
        cy.get( '[name="explanations"]' ).type("I have many things wrong with me.  But here I am still alive by the grace of God");
        cy.get( '[name="carrier"]' ).type("Medishare");
        cy.get( '[name="policy_number"]' ).type("0101010101");
        cy.get( '[name="physician"]' ).type("Dan Nitrai");
        cy.get( '[name="physician_number"]' ).type("907-123-4567");
        cy.get( '[name="family_dentist"]' ).type("Ridgeway");
        cy.get( '[name="dentist_number"]' ).type("907-123-4567");

        
    });


    it( 'Draws signature', function() {
        cy.get('#canvas')
            .scrollIntoView({offset: {top: 400, left:0}})
            .trigger('mousedown', 'center', { which:1})
            .trigger('mousemove', 'center', { which:1})
            .trigger('mousemove', 'bottom', { which:1})
            .trigger('mouseup', 'bottom', { which:1});
    });

})
