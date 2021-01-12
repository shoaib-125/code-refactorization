# code-refactorization
#1. Your thoughts about the code. What makes it amazing code. Or what makes it ok code. Or what makes it terrible code. How would you have done it. Thoughts on formatting. Logic?

-Laravel request cycle is not properly utilized , BookingRequest class have to be there for validation purpose this will minimize the much messy bundle of code.
-Other repositries have be used to for fetching data as i did for getting user by id.
-There can be more use of events to make methods more specific and dynamic,
 we can create a notification trait in there any type of notification will be managed and sent based on user, language and type, hooks can be used for that purpose. 
-BookingRepository have extra stuff which is against its conceptual usage, e.g. emails shouldn't sent from repository. It should be concerned only to access data sources
-Response message have to returned in BookingRequest and should be based on the lang selected by user.
-Same code have to in a function to avoid re-writting it.
-Methods are too long and this have to be minize and work can be divided into the functions and re-used
-There is no coding standard followed e.g. some variables are declared as camelCase and some are with snakeCase.
-Comments are not there properly.
-There have to be pre-processing of data to avoid multiple checks and conversion, for conversions or applying any formulla we can use mutators and accessors in the booking model.

Q2. Refactor it if you feel it needs formatting. The more love you put into it. The easier for us to asses.
This code is a disaster to me, this needs much and dedicated time to refactor and look beautifull , i tried to refactor with some core things ,
