/* eslint-disable  func-names */
/* eslint-disable  no-console */

const Alexa = require('ask-sdk-core');
const request = require('request');
const https = require('https');
const await = require('await');
const Promise = require('promise');

function getCategories() {
  return new Promise(((resolve, reject) => {
    var options = {
        host: 'breakthefake2019.gigamike.net',
        port: 443,
        path: '/api/categories',
        method: 'GET',
    };

    const request = https.request(options, (response) => {
      response.setEncoding('utf8');
      let returnData = '';

      response.on('data', (chunk) => {
        returnData += chunk;
      });

      response.on('end', () => {
        resolve(JSON.parse(returnData));
      });

      response.on('error', (error) => {
        reject(error);
      });
    });
    request.end();
  }));
}

function getArticles(category) {
  return new Promise(((resolve, reject) => {
    var options = {
        host: 'breakthefake2019.gigamike.net',
        port: 443,
        path: '/api/search?category=' + category,
        method: 'GET',
    };

    const request = https.request(options, (response) => {
      response.setEncoding('utf8');
      let returnData = '';

      response.on('data', (chunk) => {
        returnData += chunk;
      });

      response.on('end', () => {
        resolve(JSON.parse(returnData));
      });

      response.on('error', (error) => {
        reject(error);
      });
    });
    request.end();
  }));
}

const LaunchRequestHandler = {
  canHandle(handlerInput) {
    return handlerInput.requestEnvelope.request.type === 'LaunchRequest';
  },
  async handle(handlerInput) {
    const response = await getCategories();

    const speechText = `Welcome to the Break The Fake Council, an Alexa Skills by Team Gigamike. Please select news category. ` + response.text;
    const repromptText = `Please select news category. ` + response.text;

    return handlerInput.responseBuilder
      .speak(speechText)
      .reprompt(repromptText)
      .withSimpleCard('Welcome to the Break The Fake Council, an Alexa Skills by Team Gigamike.', speechText)
      .getResponse();
  },
};

const HelloWorldIntentHandler = {
  canHandle(handlerInput) {
    return handlerInput.requestEnvelope.request.type === 'IntentRequest'
      && handlerInput.requestEnvelope.request.intent.name === 'HelloWorldIntent';
  },
  handle(handlerInput) {
    const speechText = 'Welcome to the Break The Fake Council, an Alexa Skills by Team Gigamike.';
    const repromptText = 'Welcome to the Break The Fake Council, an Alexa Skills by Team Gigamike.';

    return handlerInput.responseBuilder
      .speak(speechText)
      .withSimpleCard('Get height in centimeters', speechText)
      .getResponse();
  },
};

const GetArticleIntentHandler = {
  canHandle(handlerInput) {
    return handlerInput.requestEnvelope.request.type === 'IntentRequest'
      && handlerInput.requestEnvelope.request.intent.name === 'GetArticleIntent';
  },
  async handle(handlerInput) {
    const category = handlerInput.requestEnvelope.request.intent.slots.Category.value;
    const response = await getArticles(category);

    const speechText = `<audio src="https://gigamike-s3.s3.amazonaws.com/newsbreak.mp3" /> Latest news from ` + category + '. ' + response.text;

    return handlerInput.responseBuilder
      .speak(speechText)
      .withSimpleCard('Welcome to the Break The Fake Council, an Alexa Skills by Team Gigamike.', speechText)
      .getResponse();
  },
};

const HelpIntentHandler = {
  canHandle(handlerInput) {
    return handlerInput.requestEnvelope.request.type === 'IntentRequest'
      && handlerInput.requestEnvelope.request.intent.name === 'AMAZON.HelpIntent';
  },
  handle(handlerInput) {
    const speechText = 'Welcome to the Break The Fake Council, an Alexa Skills by Team Gigamike.';
    const repromptText = 'Welcome to the Break The Fake Council, an Alexa Skills by Team Gigamike.';

    return handlerInput.responseBuilder
      .speak(speechText)
      .reprompt(speechText)
      .withSimpleCard('Welcome to the Break The Fake Council, an Alexa Skills by Team Gigamike.', speechText)
      .getResponse();
  },
};

const CancelAndStopIntentHandler = {
  canHandle(handlerInput) {
    return handlerInput.requestEnvelope.request.type === 'IntentRequest'
      && (handlerInput.requestEnvelope.request.intent.name === 'AMAZON.CancelIntent'
        || handlerInput.requestEnvelope.request.intent.name === 'AMAZON.StopIntent');
  },
  handle(handlerInput) {
    const speechText = 'Goodbye!';

    return handlerInput.responseBuilder
      .speak(speechText)
      .withSimpleCard('Welcome to the Break The Fake Council, an Alexa Skills by Team Gigamike.', speechText)
      .getResponse();
  },
};

const SessionEndedRequestHandler = {
  canHandle(handlerInput) {
    return handlerInput.requestEnvelope.request.type === 'SessionEndedRequest';
  },
  handle(handlerInput) {
    console.log(`Session ended with reason: ${handlerInput.requestEnvelope.request.reason}`);

    return handlerInput.responseBuilder.getResponse();
  },
};

const ErrorHandler = {
  canHandle() {
    return true;
  },
  handle(handlerInput, error) {
    console.log(`Error handled: ${error.message}`);

    return handlerInput.responseBuilder
      .speak('Sorry, I can\'t understand the command. Please say again.')
      .reprompt('Sorry, I can\'t understand the command. Please say again.')
      .getResponse();
  },
};

const skillBuilder = Alexa.SkillBuilders.custom();

exports.handler = skillBuilder
  .addRequestHandlers(
    LaunchRequestHandler,
    HelloWorldIntentHandler,
    GetArticleIntentHandler,
    HelpIntentHandler,
    CancelAndStopIntentHandler,
    SessionEndedRequestHandler
  )
  .addErrorHandlers(ErrorHandler)
  .lambda();
