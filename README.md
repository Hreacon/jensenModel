# Jensen Model

A flexible PHP model class that's very easy to adapt to different databases and structures by changing one array.

## Setup:
* In the parameters change/add variables for your table name and fields(as arrays)
* In the constructor change the tableNameFields to your table name and set it equal to an array of the fields, in order.
* You can add custom methods at the bottom to put more specific names to your particular application

## Usage:
* Constructed using an existing PDO
* Depending on the usage you usually just have to pass in an array of the values you want to save or update and the table name.
