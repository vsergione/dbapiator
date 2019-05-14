# DB APIator

DBApiator is REST API for SQL databases. In the current version it support only MySQL/MariaDB (See r). Using DBApiator one care create new records, read, update and delete existing ones using HTTP REST. 

The call syntax and message format are closely following the JSONAPI spec, but not yet fully compatible. 
   
For more details, check the documentation available [here (invalid link)](https://.....).
 
 
## How it works
The REST API is specific for every configured database. 
This is achieved by creating a configuration file which describes the database structure. 
The configuration file is structured in the following way:

    - database 
        - [table/view/stored procedure]
            - fields
            - relationships
 
A very important feature is the ability to define multiple access profiles to establish various permission levels for different audiences. 
Each access profile specifies the access level for each table, field and relation.

First level of authorisation to the database is achieved using API Keys. Each API Key is mapped to an access profile, thus providing in a single step authentication and first level of authorisation.  

                db structure \
                               => custom API    
    API KEY ->  permissions  /
 
Using this authorisation mechanism, one can achieve table level and field level granularity.
To achieve record level granularity we are currently working on a module which will implement this feature. Stay tuned.

## Install
Using GIT, clone the repo on your machine:

    git clone git@bitbucket.org:nepsis/dbapiator.git

Open /application/config/config.php and update the following variables:

    // folder where the APIs configuration files are stored
    $config["allApisDir"] = "/path/to/apis_config_files_folder";
    
    // to be used when API ID is part in the subdomain
    // eg. https://my_api_id.sub.do.main
    $config['base_domain'] = ".sub.do.main";
    
    // maximum number of recurds to update in a single call
    // in the future this will be moved in the API specification file
    $config['bulk_update_limit'] = 3;___apis  users

For user friendly configuration of APIs consider installing APIATOR Launchpad