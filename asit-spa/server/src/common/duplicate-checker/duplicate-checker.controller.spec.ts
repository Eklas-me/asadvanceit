import { Test, TestingModule } from '@nestjs/testing';
import { DuplicateCheckerController } from './duplicate-checker.controller';

describe('DuplicateCheckerController', () => {
  let controller: DuplicateCheckerController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [DuplicateCheckerController],
    }).compile();

    controller = module.get<DuplicateCheckerController>(DuplicateCheckerController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
