import winston from 'winston';

const logLevel = process.env.LOG_LEVEL || 'info';
const logFormat = process.env.LOG_FORMAT || 'json';

const formats = [
  winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }),
  winston.format.errors({ stack: true }),
];

if (logFormat === 'json') {
  formats.push(winston.format.json());
} else {
  formats.push(
    winston.format.colorize(),
    winston.format.printf(
      ({ level, message, timestamp, ...metadata }) =>
        `${timestamp} [${level}]: ${message} ${
          Object.keys(metadata).length ? JSON.stringify(metadata, null, 2) : ''
        }`
    )
  );
}

export const logger = winston.createLogger({
  level: logLevel,
  format: winston.format.combine(...formats),
  transports: [
    new winston.transports.Console(),
  ],
});

// Add file transport if enabled
if (process.env.LOG_TO_FILE === 'true') {
  const logFilePath = process.env.LOG_FILE_PATH || './logs/app.log';
  logger.add(new winston.transports.File({ filename: logFilePath }));
}

export default logger;
