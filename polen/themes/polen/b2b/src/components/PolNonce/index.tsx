import React from "react";
import { getNonce } from "services";

export default function ({ onChange = null }) {
  const [nonce, setNonce] = React.useState(null);
  React.useEffect(() => {
    getNonce().then((res) => {
      setNonce(res.data);
      if (onChange) {
        onChange({
          target: {
            name: "security",
            value: res.data,
          },
        });
      }
    });
  }, []);
  return <input type="hidden" name="security" defaultValue={nonce} />;
}
