Data import extension for eZPublish
=========

Extension : data_import
Requires  : eZ Publish 4.x.x
Authors   : Marius Eliassen (me[_at_]ez.no),
            Philipp Kamps (pkamps[_at_].mugo.ca)

Summary   :
The purpose of that extension is to import data from a
given data source (like xml/csv documents) into the eZ
Publish content tree. This extension is under the GPL.

Concepts :
We choose a object oriented approach. Developers need to
implement a SourceHandler that understands the given data
source. The handler is completely independent from the
import operators. The import operators contain the logic
how to create/update the content nodes in eZ Publish. For
simple import operations it is not required to override
the import operator.

Import Process :
Each import process starts with an eZ command line script.
It only gets an instance of a SourceHandler and an
ImportOperator and then runs the ImportOperator.

Get started :
Here a quick description how to get started with that
extension - so you can decide if it usefull to you.

- install a vanilla eZ Publish 4.0.0 or higher
 
- during the install select the ezwebin package
  
- install this extension (see doc/INSTALL)

- run 2 example imports
  prompt> php extension/data_import/scripts/run.php -i ImportOperator -d XMLFolders
  prompt> php extension/data_import/scripts/run.php -i ImportOperator -d XMLImages

alternatively

  prompt> php extension/data_import/scripts/run.php -i ImportOperator -d CSVFolders
  prompt> php extension/data_import/scripts/run.php -i ImportOperator -d CSVImages


How it works  :

The extension is based on top of the eZ Publish API. The 2 main
php classes are ImportOperator.php and SourceHandler.php. The
ImportOperator contains the logic how to create or update content
in eZ Publish. The SourceHandler is responsible to read and
understand given data that need to be imported. So typically you
only have to create your own SourceHandler to understand your
specific CSV or XML file. In most cases it is not required to
override any ImportOperator functionality.

The extension is using eZ Publish's "Remote Id" to identify
imported data. Each node in eZ Publish has 3 ids:

- node id
- object id
- remote id

Node id and object id are easily readable from the admin interface.
The remote id is hidden.
When importing data from a CSV file the data import extension will
create new nodes in eZ Publish - eZ Publish will automatically generate
a node id and an object id. The remote id is set by the data import
extension. That remote id should identify a row in your CSV file or
a XML node in an XML document. In order to set the remote id you have
to implement the function "getDataRowId" in your SourceHandler.
The reason why the extension is using the remote id is simple, it allows
the ImportOperator to identify already imported data. For example, if
you import a CSV file a second time the import Operator will recognize
the existing remote ids in eZ Publish and instead of creating new
nodes it will update your previously imported nodes. That also works
if the node got moved to a different location or is in the trash. It
still has the same remote id and therefor gets recognized by the
ImportOperator.

The location where to place imported nodes only get during the creation
process. So if you create new nodes with new remote ids the ImportOperator
is calling the method "getParentNodeId" in your source handler. You have
to return an existing node id for the parent node id. That can be a newly
created node that was created by a previous line in your CSV file. So order
matters here. Your CVS file should create potential parent nodes first.
In case the ImportOperator recognizes the remote id - it will only update
the node content - it is not calling the "getParentNodeId" at all and therefor
is not able to move existing nodes. You would need to use a different
ImportOperator in order to support that szenario.

In order to import the content in eZ Publish attributes the ImportOperator
is using the API method "fromString". That method is implemented for all
standard eZ Publish Datatype attributes. For custom datatype you need to check
if it has an implementation for the "fromString" method before using the
data import extension with the custom datatype.
