import React from "react";
import { Helmet } from "react-helmet";
import { SEO } from "interfaces";

const PolSEO: React.FC<SEO> = ({
  title = "Polen para Empresas",
  description = "Aproveite o poder das celebridades para espalhar a emoção e potencializar o seu negócio! Tudo com muita rapidez e facilidade para melhor atender à sua empresa.",
  canonical = "https://polen.me/empresas",
  site_name = "Polen.me",
  url = "https://polen.me/empresas",
  type = "site",
  image = `https://polen.me/polen/uploads/2021/06/cropped-logo-192x192.png`,
  video,
  keywords,
  author,
}) => {
  return (
    <>
      <Helmet
      htmlAttributes={{
        lang: 'pt-BR',
      }}
      >
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-title" content={site_name} />
        <meta name="apple-touch-fullscreen" content="yes" />

        <link
          rel="icon"
          href="https://polen.me/polen/uploads/2021/06/cropped-logo-32x32.png"
          sizes="32x32"
        />
        <link
          rel="icon"
          href="https://polen.me/polen/uploads/2021/06/cropped-logo-192x192.png"
          sizes="192x192"
        />
        <link
          rel="apple-touch-icon"
          href="https://polen.me/polen/uploads/2021/06/cropped-logo-180x180.png"
        />

        <meta name="theme-color" content="#212121" />

        <meta name="title" content={title} key="title" />
        <meta name="description" content={description} key="description" />
        <link rel="canonical" href={canonical} key="canonical" />
        <meta name="keywords" content={keywords} key="keywords" />
        <meta name="author" content={author} key="author" />

        <meta property="og:title" content={title} key="og-title" />
        <meta
          property="og:description"
          content={description}
          key="og-description"
        />
        <meta property="og:url" content={url} key="og-url" />
        <meta property="og:image" content={image} key="og-image" />
        <meta property="og:locale" content="pt-BR" key="og-locale" />
        <meta property="og:site_name" content={site_name} key="og-site-name" />
        {type && <meta property="og:type" content={type} key="og-type" />}
        {video && <meta property="og:video" content={video} key="og-video" />}
        <title>Polen B2B</title>
      </Helmet>
    </>
  );
};

export default PolSEO;
