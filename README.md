# DB APIator

DBApiator is REST API for accessing and working with data from SQL databases.
 
By using the REST API it is possible to:
- retrieve single records by ID
- retrieve multiple records while using filtering. Options available for multiple records retrieval
  - paging
  - filtering (searching)
  - sparse field selection
  - ordering
  - inclusion of related records
- retrieval of related records (single or multiple depending on the type of relation: 1:1 or 1:n)
- create records: single or batch 
- update records: single or batch
- delete records: single or batch (by providing a filter criteria)
- self generated documentation
- access control policies down to field level granularity

Read operations are supported for both tables and views. Write operations (create, update, delete) are supported only for tables
  
The basic call syntax and message format is following the JSONAPI spec.   
   
For more details, check the documentation available [here (invalid link)](https://.....).
 
## How it works
The REST API is specific for every database. This is achieved by creating a configuration file which describes the database structure.

A configuration file is structured in the following way:

    - database 
        - [table/view/stored procedure]
            - fields
            - relationships
 

## Install
Using GIT, clone the repo on your machine:

    > git clone git@bitbucket.org:nepsis/dbapiator.git 

Using the CLI go to installation directory and execute
    
    > setup.php install

or just

    > setup.php
    
to see available options

You can create your first REST API either as part of the install process or by running

    > setup.php setupdb  
    
## Usage
Once a REST API has been created, it is available at the following URL:
https://your_host/dbapi_install_location/v2/project_name

For every API the available endpoints are generated according to the following rules
- .../project_name/v2/resource_name
    - GET: retrive records from resource_name (where resource_name is the name of a table or view). The following query parameters can be used:
        - page[offset]
        - page[limit]
        - fields[resource_name] 
        - sort[resource_name]
        - include: comma separated list of relationship names
        - filter 
    - POST: create new records in table resource_name
- ../project_name/v2/resource_name/ID
    - GET: retrieve record from table/view resource_name identified by id ID....
    - PATCH: update record from table/view resource_name identified by id ID....
    - DELETE: delete record from table/view resource_name identified by id ID....
- ../project_name/v2/resource_name/ID/relatioship_name
    - GET: retrieve related record(s) ... Parameters
        - for 1:1 relations
            - fields[resource_name]
            - include
        - for 1:n relations
             - page[offset]
             - page[limit]
             - fields[resource_name] 
             - sort[resource_name]
             - include: comma separated list of relationship names
             - filter
    - PATCH: update related record(s) ....
    - DELETE: delete record from table/view resource_name identified by id ID....

For a full list of available endpoints and requests check:
https://your_host/dbapi_install_location/swagger/project_name

Also check JSON API (https://jsonapi.org/) specification to get a better understanding 