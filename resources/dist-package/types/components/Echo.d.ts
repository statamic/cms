export default Echo;
declare class Echo {
    configCallbacks: any[];
    bootedCallbacks: any[];
    config(callback: any): void;
    booted(callback: any): void;
    start(): Promise<void>;
    echo: import("laravel-echo").default<keyof {
        reverb: {
            connector: import("laravel-echo/dist/connector").PusherConnector;
            public: import("laravel-echo/dist/channel").PusherChannel;
            private: import("laravel-echo/dist/channel").PusherPrivateChannel;
            encrypted: import("laravel-echo/dist/channel").PusherEncryptedPrivateChannel;
            presence: import("laravel-echo/dist/channel").PusherPresenceChannel;
        };
        pusher: {
            connector: import("laravel-echo/dist/connector").PusherConnector;
            public: import("laravel-echo/dist/channel").PusherChannel;
            private: import("laravel-echo/dist/channel").PusherPrivateChannel;
            encrypted: import("laravel-echo/dist/channel").PusherEncryptedPrivateChannel;
            presence: import("laravel-echo/dist/channel").PusherPresenceChannel;
        };
        'socket.io': {
            connector: import("laravel-echo/dist/connector").SocketIoConnector;
            public: import("laravel-echo/dist/channel").SocketIoChannel;
            private: import("laravel-echo/dist/channel").SocketIoPrivateChannel;
            encrypted: never;
            presence: import("laravel-echo/dist/channel").SocketIoPresenceChannel;
        };
        null: {
            connector: import("laravel-echo/dist/connector").NullConnector;
            public: import("laravel-echo/dist/channel").NullChannel;
            private: import("laravel-echo/dist/channel").NullPrivateChannel;
            encrypted: import("laravel-echo/dist/channel").NullEncryptedPrivateChannel;
            presence: import("laravel-echo/dist/channel").NullPresenceChannel;
        };
        function: {
            connector: any;
            public: any;
            private: any;
            encrypted: any;
            presence: any;
        };
    }> | undefined;
}
