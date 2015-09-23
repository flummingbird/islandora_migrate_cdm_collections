# Islandora CONTENTdm Collection Migrator

A utility module for exporting collection configuration data from a CONTENTdm instance and then creating Islandora collection objects using this data. This module will only be of use to sites that are migrating from CONTENTdm to Islandora.

## Introduction

This module has two main parts: 1) a command-line PHP script that gathers configuration data for collections on a CONTENTdm server and 2) a drush script for creating collections in an Islandora instance using that data. The PHP script outputs the collection data into a tab-separated file so it can be modified prior to being used by the drush script.

## Usage

### Step 1: Getting collection data from your CONTENTdm server

In this step, you run `get_collection_data.php`, located in this Git repository's scripts directory. Detailed instructions are provided within the script itself, but in a nutshell, you configure a few variables and then run the script. If you have access to your CONTENTdm server's command line and run the script there, the output of the script will contain the alias, title, and description for each CONTENTdm collection, plus the collection's thumbnail image. If you don't have access to your CONTENTdm server's shell (e.g., your CONTENTdm is hosted by OCLC), you can run the script from any computer that has PHP installed, but the output will only contain the collections' aliases and titles. In both cases, you run the script by issuing the following command:

```
php get_collection_data.php
```

Running this script on the CONTENTdm server's command line creates a file containing one tab-delimited row per collection, for example (with tabs represented here by [\t]):

```
ubcCtn[\t]Chinatown News[\t]<p>The <em>Chinatown News</em> was an English-language biweekly magazine.</p>[\t]chn2.jpg
```

Thumbnail images identified in the last field are copied into the output directory within subdirectories named after the collection alias (the value in the first field). If run outside the CONTENTdm server, using the CONTENTdm Web API, the lines in the file only contain the first two fields, and the thumbnail images are not copied: 
 
```
ubcCtn[\t]Chinatown News
```

You are free to edit the output file before running it through the drush script as long as you don't change structure of the fields and don't add any line breaks. Keep in mind that all HTML markup is stripped from the description before it is added to the collection objects' DC datastream. Markup is not stripped from node description fields (see below). Also, if you did not have desciptions or thumbnails for your collections in CONTENTdm, or you had to run `get_collection_data.php` using the 'api' method, you can add them to the demlimited file. If you want to add or change thumbnails, make sure that the file names in the fourth columns of the delimited file match the image files in the collection directories.

There is another option in `get_collection_data.php` that warrants explanation. If you set the `$get_collection_field_info` variable to TRUE, each of the Islandora collection objects created by the drush script will have a datastream with the DSID 'CDMFIELDINFO'. This datastream is not required but may prove useful in your migration process or for some unforseen purpose in the future. The datastream will contain a snapshot, in JSON format, of the collection's metadata configuration.

### Step 2: Importing collection objects into Islandora

Once you have run `get_collection_data.php` and copied its output to your Islandora server, you must run the drush command `drush create-islandora-collections-from-cdm` to create the collection objects (and optionally, Drupal nodes corresponding to the objects) described in the output of `get_collection_data.php`.

Two examples of the drush command are:

```
drush --user=admin create-islandora-collections-from-cdm --namespace=mynamespace --parent=islandora:root  --input=/tmp/cdmcollectiondata/collection_data.tsv --create_node_with_content_type=islandora_collections
```
or its short form:

```
drush --user=admin cicfc --namespace=mynamespace --parent=islandora:root  --input=/tmp/cdmcollectiondata/collection_data.tsv --create_node_with_content_type=mycontenttype
```

Options are:

 * `--input`: (Required) The absolute path to the tab-delimited file generated by the get_collection_data.php script.
 * `--namespace`: (Optional) The namespace to use for the new collections. Defaults to "islandora". Use the special value "use_alias" if you want each collection object to use its CONTENTdm alias as its namespace (note that the alias will be converted to lower case and all non-alphanumeric characters converted to '-').
 * `--parent`: (Optional) The collection to which the new collections should be added. Defaults to the root Islandora repository PID.
 * `--create_node_with_content_type`: (Optional) Create a node for each collection with the specified Drupal content type machine name. Defaults to "page". The content type must exist and must be configured as described below.

If there are no thumbnail images in the collection data directory, or if the drush script can't find an image identified in the tab-delimited file (due to a mismatching filename, for example), the newly created collection is assigned the default thumbnail image provided by the Islandora Collection Solution Pack.


## Creating Drupal nodes for collections

If you include the `--create_node_with_content_type=mycontenttype` option, the drush script will create a Drupal node of the specified content type corresponding to each collection object. You must create this content type before running the drush command, but the script will check for its existence and exit if the content type (or the required fields) don't exist. The content type must contain the following fields:

 * title
 * cdm_alias (field type = Text, widget = Text field)
 * description (field type = Long text, widget = Textarea (multiple rows); make the default input format "Full HMTL"))
 * thumbnail (field type = Image, widget = Image)

For all fields, use 1 in the "Number of values" configuration option. The field configuration for your content type should look like this:

![Islandora CONTENTdm Collection Migrator content type field configuration](https://dl.dropboxusercontent.com/u/1015702/linked_to/islandora_migrate_cdm_collections_content_type_config.png)

Your content type may have additional fields if you wish, but the ones described above are required by this module.

The nodes will be published, not sticky, be owned by user ID 1, and use your site's default language. If you want to change these settings, you'll need to do so manually or using [Views Bulk Operations](https://www.drupal.org/project/views_bulk_operations). If the input data does not contain descriptions or thumbnails, values for these fields will not be added to the nodes.

## Requirements

This module requires the following modules/libraries:

* [Islandora](https://github.com/islandora/islandora)
* [Islandora Collection Solution Pack](https://github.com/Islandora/islandora_solution_pack_collection)

## Current maintainer

* [Mark Jordan](https://github.com/mjordan)

## License

[GPLv3](http://www.gnu.org/licenses/gpl-3.0.txt)
