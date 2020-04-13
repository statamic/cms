require('../bootstrap/globals');

test('it tidies urls', () => {
    expect(tidy_url('foo/bar')).toBe('foo/bar');
    expect(tidy_url('foo//bar')).toBe('foo/bar');
    expect(tidy_url('foo///bar')).toBe('foo/bar');
    expect(tidy_url('foo////bar')).toBe('foo/bar');
    expect(tidy_url('http://foo//bar')).toBe('http://foo/bar');
    expect(tidy_url('https://foo//bar')).toBe('https://foo/bar');
    expect(tidy_url('notaprotocol://foo//bar')).toBe('notaprotocol://foo/bar');
});
