# Slack Web Integrations
There are two main ways to integrate a website with Slack:
+ [Incoming Webhooks](#incoming-webhooks)
+ [Slash Commands](#slash-commands)

Both of these can be packaged into a Slack app, but I did not, so I won't be covering that here.

## Slack API Guide
Slack has a great API Guide [available here](https://api.slack.com) in its entirety. I'll link to specific sections to reference as I go along.

## Incoming Webhooks
[Slack API Guide on Incoming Webhooks](https://api.slack.com/incoming-webhooks)

Slack's description of these is:

>Incoming Webhooks are a simple way to post messages from external sources into Slack. They make use of normal HTTP requests with a JSON payload that includes the message text and some options. Message Attachments can also be used in Incoming Webhooks to display richly-formatted messages that stand out from regular chat messages.

### Setup
Before you can do anything significant, you'll need to register your webhook with your Slack team. Simply [head to your team's admin page](https://my.slack.com/services/new/incoming-webhook/) to add this integration, should you have permissions to do so.

Select the channel your webhook will be posting to most often. There are ways to change the channel, outlined later on.

You'll be shown a nice little example to get started. You can skip that for now. That's why you're reading this guide!

Further down the page are options for the identity of the webhook in Slack...
+ Post to Channel
 + This is the exact same setting as the first page. This should be the channel you plan on posting to most often. It can be changed per-message programmatically later on, though.
+ Webhook URL
 + You'll want to keep this handy. This URL is where you'll send your JSON payload to later on. It acts as your key as well. You can regenerate this should it become compromised.
+ Descriptive Label
 + This is merely for you looking at your list of integrations on the Slack admin page and has no effect on the end user.
+ Customize Name
 + This is the username your integration will post as. You can change this programmatically later on.
+ Customize Icon
 + This is your webhook's profile image. You can change this programmatically to a Slack emoji later.
   + [You can create custom Slack emoji here](https://my.slack.com/customize/emoji)

Save your settings. Now, let's get to sending a message, shall we?

### Sending a Message
#### JSON Message Body
At its simplest, a Slack message just has the parameter `text`, so the JSON would look something like this:
```json
{"text": "This is the message to be sent."}
```
##### Links
can be added in the form `<URL|Hyperlinked Text>`. So if you'd like to send [Open Google!](https://www.google.com]):
```json
{"text": "<https://www.google.com|Open Google!>"}
```
##### Emoji
can be included using standard Slack :syntax: :
```json
{"text": "This has a :grinning: emoji!"}
```
##### Message Formatting
also works the same as in Slack:
```json
{"text": "*Bold* _italic_ ~strike~ `code`"}
```
If you'd like the same message to be sent verbatim (not formatted), you'll have to specify that.
```json
{
  "text": "*Bold* _italic_ ~strike~ `code`",
  "mrkdwn": false
}
```
##### Attachments
are also supported. [Visit the Slack API to read on how to attach things.](https://api.slack.com/docs/message-attachments)

##### Channel, Username, and Icon
can be changed by adding the `channel`, `Username`, and `icon_emoji` parameters. You can also enable linking to #channels and @users with the `link_names` parameter:
```json
{
  "text": "@momma always told me that _Life is like a box of chocolates..._",
  "channel": "#runners",
  "link_names": 1,
  "username": "Forrest",
  "icon_emoji": ":fried_shrimp:"
}
```

##### Message Builder
Slack provides a **[Message Builder Tool](https://api.slack.com/docs/messages/builder)** to aid you in building messages and formatting the JSON properly.

##### URL Encode
**You must URL Encode the JSON String with `urlencode()` before sending**

#### CURL Request
Now you'll have to send the request via POST. On PHP, that's easily done with CURL. Set the `Content-Type` to `JSON`, and set your payload to your JSON message string (I'll call it `$data_string`).

```php
$ch = curl_init('https://hooks.slack.com/services/QWERTYUIOP/kdlafdhsajkfheuahfdska78fheuaf'); //Your Webhook URL
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data_string))
);
$result = curl_exec($ch);
```

## Slash commands
[Slack API Guide on Slash commands](https://api.slack.com/slash-commands)

Slack defines commands as...
> Messages that start with a slash / are commands and will behave differently from regular messages. For example, you can use the "topic" command to change your current channel's topic to "Hello!" by typing /topic Hello!. When you type /remind me in 10 minutes to drink a glass of water the command will set a reminder for you to drink a glass of water in 10 minutes

There are three types of commands: Built-in, like `/topic`, Custom (which we'll be writing), and App (for use with Slack Apps).

Slack defines custom commands as...
> Every team has the ability to create their own custom commands that they can use on their team. For example, you may want to do something very specific like query your cloud-based employee directory, or deploy code to your servers. These commands are simple to create for your team and don't have many restrictions.

> When commands are executed, we send a request to your SSL-enabled server. Commands are executed from Slack's own servers, so your server needs to be accessible to Slack. Don't worry, we provide a means to verify the request is coming from Slack.

In the example I'll be using, this command will handle the admin tasks of a food order taking webapp.

### Setup
Before you can do anything significant, you'll need to register your slash command with your Slack team. Simply [head to your team's admin page](https://my.slack.com/apps/new/A0F82E8CA-slash-commands) to add this integration, should you have permissions to do so.

Enter your command with the `/`. In our example, we'll use `/ordersadmin`. It cannot have spaces and cannot be blank.

You'll be shown a nice little example to get started. You can skip that for now. That's why you're reading this guide!

Skip down to the _Integration Settings_.

+ Command
 + This is what you just entered. This is what the user enters on Slack to invoke the command.
+ URL
 + This is the URL Slack will call once the slash command is invoked.
+ Method
 + This is how Slack will send its payload. Out example will keep it as POST.
+ Token
 + Very important. This is how you'll verify that Slack is who it says it is.
+ Customize Name and Customize Icon
 + The username and profile picture shown to the Slack users. This can be changed programmatically.
+ Autocomplete help text
 + This will be shown to users when they enter your command to help them with how to use it. Take a look at the included graphic to see what each parameter is.
+ Escape channels, users, and links
 + Enabling will escape all @users and #channels to include their IDs. Only enable if you'll be doing something that requires keeping track of users or channels. In most cases, this isn't necessary. It will be disabled in our example.
+ Descriptive Label
 + This has no effect on the end user. It simply helps you identify this integration in your list on the admin page.


 Save your settings. Now, let's do something useful!

### Payload
Slack will send the URL you indicated a payload that looks like this:
```
token=SOMETOKEN
team_id=T0001
team_domain=example
channel_id=C2147483705
channel_name=test
user_id=U2147483697
user_name=Steve
command=/weather
text=94070
response_url=https://hooks.slack.com/commands/1234/5678
```
`team_id`, `channel_id`, `channel_name`, `user_id`, and `user_name` tell you what team, channel, and user sent the slash command.

`token` is what should be verified with the token Slack gave you on the configuration page. If they don't match, _these are not the data you are looking for_.

`command` tells you what slash command was invoked. That way, you can use the same script for multiple commands.

`text` is everything that was entered that was not the command itself. These can be things like extra parameters or info for your script to run.

`response_url` is where you should send your response via POST if you want to send additional details, or your script cannot or will not respond within 3000 milliseconds. You can respond with this up to 5 times within 30 minutes of the command's invocation.

### Responses
To respond, all you have to do is return (or send to the `response_url`) a string.

The simplest way is to send plain text.

If you want to make your message look decent or customize other aspects of it, return a JSON string. Take a look at the [JSON Message Body](#json-message-body) section of the incomming webhooks guide for how to format these.

Slack expects an HTTP 200 response regardless of whether your script returns something.

#### In-Channel vs Ephemeral
Two options on how messages are in Slack exist. `in_channel` messages display the initial message from the user and the response to everyone in the channel. `ephemeral` messages hide the command message and the response from everyone except the invoking user.

The default option is `ephemeral`.

To change the message type, simply include the `response_type` parameter in your JSON:
```json
{
  "response_type": "in_channel",
  "text": "This will be seen by everyone! Hi Mom!"
}
```

### Example
Take a look at [this PHP Script](slashcommand_ex.php) to see a slash command in action.

# Disclaimer
This guide and these examples are not affiliated with Slack.
