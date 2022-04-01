require("dotenv").config({
  path: `.env.${process.env.NODE_ENV}`,
});

module.exports = {
  pathPrefix: "empresas",
  assetPrefix: "polen/themes/polen/b2b",
  siteMetadata: {
    title: `Polen B2B`,
    siteUrl: `https://polen.me/empresas`,
  },
  plugins: [
    "gatsby-plugin-sass",
    "gatsby-plugin-react-helmet",
    "gatsby-plugin-resolve-src",
    {
      resolve: `gatsby-plugin-google-fonts-with-attributes`,
      options: {
        fonts: [`Inter`, `source sans pro\:400,600,700`],
        display: "swap",
        attributes: {
          rel: "stylesheet preload prefetch",
        },
      },
    },
    // {
    //   resolve: "gatsby-plugin-google-tagmanager",
    //   options: {
    //     id: "GTM-T65KTZ3",

    //     // Include GTM in development.
    //     //
    //     // Defaults to false meaning GTM will only be loaded in production.
    //     includeInDevelopment: false,

    //     // datalayer to be set before GTM is loaded
    //     // should be an object or a function that is executed in the browser
    //     //
    //     // Defaults to null
    //     defaultDataLayer: { platform: "gatsby" },

    //     // Specify optional GTM environment details.
    //     // gtmAuth: "YOUR_GOOGLE_TAGMANAGER_ENVIRONMENT_AUTH_STRING",
    //     // gtmPreview: "YOUR_GOOGLE_TAGMANAGER_ENVIRONMENT_PREVIEW_NAME",
    //     // dataLayerName: "YOUR_DATA_LAYER_NAME",

    //     // Name of the event that is triggered
    //     // on every Gatsby route change.
    //     //
    //     // Defaults to gatsby-route-change
    //     // routeChangeEventName: "YOUR_ROUTE_CHANGE_EVENT_NAME",
    //     // Defaults to false
    //     enableWebVitalsTracking: true,
    //     // Defaults to https://www.googletagmanager.com
    //     // selfHostedOrigin: "YOUR_SELF_HOSTED_ORIGIN",
    //   },
    // },
  ],
};
