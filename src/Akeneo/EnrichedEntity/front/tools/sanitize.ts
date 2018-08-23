export default (value: string) => {
  const regex = /[a-zA-Z0-9_]/;

  return value
    .split('')
    .filter((char: string) => char !== ' ')
    .map((char: string) => (char.match(regex) ? char : '_'))
    .join('')
    .toLocaleLowerCase();
};