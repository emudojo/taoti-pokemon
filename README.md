# taoti-pokemon

This is a small Drupal 9 repo to demo module development and a little bit of a migration.

this has a working ddev env preconfigured, so just doing ddev start will prob get u up and running

no front end, just using olivero

1 module needs to be enabled

**`code_review_module`**

this depends on features, ignore all the commerce stuff, my plan was to build a store but I don't have time to do that now.

this module will create a new migration  called migrate_pokemons

can be run using `**drush mim migrate_pokemons**`

it will import content from the pokeapi.co website using the graphQL API and generarte pokemon items stored in the pokemon content type.
Module has a self contained config that was created using features

I used the migrate_source_graphql module, but turns out it can't do nested fields with arguments, and had a bug in the way data was returned, so
I forked it, porb I'll cleaned it more and provide a patch, I rewrote most of the core functions and why is in the fork folder.

Images for pokemons are fetched from another API this time old faslhioned REST, as for some reason graphql does NOT exposes images.... for this I created
a couple of custom process plugins for the migration, nothing fancy,]


All and all, very simple, but tool me a few hours to modify and create a workigng version of the source plugin.