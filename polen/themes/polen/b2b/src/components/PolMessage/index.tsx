import React, { MouseEventHandler } from "react";
import { useAppContext } from "context";
import { X, AlertCircle, CheckCircle } from "react-feather";

type Props = {
  title?: string;
  message: string;
  handle: MouseEventHandler;
};

export const MESSAGE_TYPES = {
  MESSAGE: "message",
  ERROR: "error",
};

export const polMessageInitial = {
  show: false,
  type: "",
  title: "",
  message: "",
};

const timeToMessage = 10; //em segundos

const Message: React.FC<Props> = ({ title = "Sucesso!", message, handle }) => {
  const [visible, setVisible] = React.useState(false);
  React.useEffect(() => {
    setVisible(true);
    let interval;
    interval = setTimeout(handle, 1000 * timeToMessage);

    return () => {
      clearTimeout(interval);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);
  return (
    <div
      id="message-box"
      className={`message-box success${visible && " show"}`}
    >
      <div className="row">
        <div className="col-md-12 mb-2">
          <CheckCircle size={16} color="var(--bs-low-pure)" />
        </div>
        <div className="col-md-12">
          <h4 className="message-title">{title}</h4>
          <p className="message-text mt-1">{message}</p>
        </div>
      </div>
      <button className="message-close" onClick={handle}>
        <X size={16} color="var(--bs-low-pure)" />
      </button>
    </div>
  );
};

const Error: React.FC<Props> = ({ message, handle }) => {
  const [visible, setVisible] = React.useState(false);
  React.useEffect(() => {
    setVisible(true);
    let interval;
    interval = setTimeout(handle, 1000 * timeToMessage);

    return () => {
      clearTimeout(interval);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);
  return (
    <div id="message-box" className={`message-box error${visible && " show"}`}>
      <AlertCircle size={16} color="var(--bs-high-pure)" />
      <p className="message-text px-1">{message}</p>
      <button className="message-close" onClick={handle}>
        <X size={16} color="var(--bs-high-pure)" />
      </button>
    </div>
  );
};

const PolMessage = () => {
  const context = useAppContext();
  let message = context.polMessage;
  if (message && message.show) {
    return message.type === MESSAGE_TYPES.ERROR ? (
      <Error
        message={message.message}
        handle={() => context.setPolMessage(polMessageInitial)}
      />
    ) : (
      <Message
        title={message.title}
        message={message.message}
        handle={() => context.setPolMessage(polMessageInitial)}
      />
    );
  }
  return <></>;
};

export function showMessage(context, type, title, message) {
  context.setPolMessage({
    show: true,
    type,
    title,
    message,
  });
}

export default Object.assign(PolMessage, {
  Message: Message,
  Error: Error,
});
