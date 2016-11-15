# Insightly client
There is an insightly client provided in the application. To use the client, inject `insightly_client` in your class.

## Structure
### Methods
For every insightly method that is needed in insightly, a public method should be available in the insightly client.
 
### Result parsing
For every call, a result handler should be written. This result handler will handle the call and eventually parse returned data
using a parser. This parser should be stored in the [Parser directory](src/Insightly/Parser).